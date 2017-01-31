<?php

namespace App\Http\Controllers;

use App\Events\NewConfigurationFile;
use App\Requisicao;
use App\Subrede;
use App\TipoSubrede;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use SSH;

class PfsenseController extends Controller
{
    /**
     * Reconstroi o static map (lista de ip vinculados a macs)
     * @return bool True em todos os casos.
     */
    private static function rebuildStaticMap($tipoSubredeId, $pathConfigFile)
    {
        // Recupera os IDs de todas as subredes do mesmo tipo
        $subredes = TipoSubrede::find($tipoSubredeId)->subredes;

        info('Info', ['Ids subredes' => $subredes->pluck('id'), 'Tipo subrede' => $tipoSubredeId]);

        // Recupera todas as requisições ativas de um mesmo tipo de subrede (LAN ou NAT) ordenados pelo IP
        $requestsAllowed = Requisicao::where('status', 1)->whereIn('subrede_id', $subredes->pluck('id'))->orderBy(DB::raw('INET_ATON(ip)'))->get();

        // Carrega o arquivo de configuração de acordo com o path informado
        $configXML = simplexml_load_file($pathConfigFile);

        // Apaga os vínculos [IP, MAC] que existiam antes
        unset($configXML->dhcpd->lan->staticmap);

        // Inicia o iterador
        $iterator = 0;

        // Para cada requisição liberada é criada uma nova entrada no static map
        foreach ($requestsAllowed as $request)
        {
            $configXML->dhcpd->lan->staticmap[$iterator]->mac = $request->mac;
            $configXML->dhcpd->lan->staticmap[$iterator]->ipaddr = $request->ip;
            $configXML->dhcpd->lan->staticmap[$iterator]->hostname = "Requisicao-" . $request->id;
            $configXML->dhcpd->lan->staticmap[$iterator]->descr = $request->usuarioNome . '-' . $request->descricao_dispositivo;
            $configXML->dhcpd->lan->staticmap[$iterator]->addChild("arp_table_static_entry");
            $configXML->dhcpd->lan->staticmap[$iterator]->addChild("filename");
            $configXML->dhcpd->lan->staticmap[$iterator]->addChild("rootpath");
            $configXML->dhcpd->lan->staticmap[$iterator]->addChild("defaultleasetime");
            $configXML->dhcpd->lan->staticmap[$iterator]->addChild("maxleasetime");
            $configXML->dhcpd->lan->staticmap[$iterator]->addChild("gateway");
            $configXML->dhcpd->lan->staticmap[$iterator]->addChild("domain");
            $configXML->dhcpd->lan->staticmap[$iterator]->addChild("domainsearchlist");
            $configXML->dhcpd->lan->staticmap[$iterator]->addChild("ddnsdomain");
            $configXML->dhcpd->lan->staticmap[$iterator]->addChild("ddnsdomainprimary");
            $configXML->dhcpd->lan->staticmap[$iterator]->addChild("ddnsdomainkeyname");
            $configXML->dhcpd->lan->staticmap[$iterator]->addChild("ddnsdomainkey");
            $configXML->dhcpd->lan->staticmap[$iterator]->addChild("tftp");
            $configXML->dhcpd->lan->staticmap[$iterator]->addChild("ldap");
            ++$iterator;
        }

        // Salva o arquivo de configuração, sobreescrevendo o antigo;
        $configXML->saveXML($pathConfigFile);

        return true;
    }

    /**
     * Atualiza o arquivo de configuração no pfSense
     */
    public static function refreshPfsense($subrede_id)
    {
        $pfsenseConnection = 'pfsense';
        $tipoSubrede = Subrede::find($subrede_id)->tipo_subrede_id;

        if($tipoSubrede == 1) // Redes LAN
        {
            $folder = 'lan';
            $pfsenseConnection .= 'LAN';
        }
        else // Redes NAT
        {
            $folder = 'nat';
            $pfsenseConnection .= 'NAT';
        }

        $configFile = storage_path('app/config/' . $folder . '/config.xml');

        // Realiza o backup do arquivo de configuração anterior
        if(File::exists($configFile))
        {
            $lastModified = File::lastModified($configFile);
            File::copy($configFile, storage_path('app/config/' . $folder . '/config-' . $lastModified . '.xml'));
        }

        try
        {
            // Obtém o arquivo de configurações mais recente
            SSH::into($pfsenseConnection)->get("/cf/conf/config.xml", $configFile); // Path do arquivo local e path do arquivo remoto

            // Modificar o arquivo de configuração, adicionando o novo static map
            PfsenseController::rebuildStaticMap($tipoSubrede, $configFile);

            // Envia o novo arquivo de configuração
            SSH::into($pfsenseConnection)->put($configFile, '/cf/conf/config.xml');

            // Remove o cache da configuração e reinicia o firewall com a nova configuração
            $commands = ["rm /tmp/config.cache", "/etc/rc.reload_all"];
            SSH::into($pfsenseConnection)->run($commands);

            event(new NewConfigurationFile());
        }
        catch (\Exception $e)
        {
            logger()->error('Erro pfSense', ['mensagem' => $e->getMessage(), 'codigo' => $e->getCode()]);
            return false;
        }

        return true;
    }
}
