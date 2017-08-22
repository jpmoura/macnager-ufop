<?php

namespace App\Http\Controllers;

use App\Events\NewConfigurationFile;
use App\Events\RequestWake;
use App\Events\RequestWakeFailed;
use App\Requisicao;
use App\TipoSubrede;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use SSH;

class PfsenseController extends Controller
{
    /**
     * Reconstrói o arquivo de configuração com a nova tabela ARP
     * @return bool True se o arquivo de configuração foi modificado e salvo corretamente e False caso contrário.
     */
    private static function rebuildStaticMap($pathConfigFile)
    {
        // Carrega o arquivo de configuração de acordo com o path informado
        $configXML = simplexml_load_file($pathConfigFile);

        if(!$configXML)
        {
            // Se o arquivo não foi carregado, aborta a operação
            logger()->error('Erro pfSense', ['mensagem' => "Não foi possível carregar o arquivo de configuração"]);
            return false;
        }
        else
        {
            // Recupera os IDs de todas as subredes do mesmo tipo
            $subredesLAN = TipoSubrede::find(1)->subredes;
            $subredesNAT = TipoSubrede::find(2)->subredes;

            // Recupera todas as requisições ativas de subredes LAN ordenados pelo IP
            $lanRequestsAllowed = Requisicao::where('status', 1)->whereIn('subrede_id', $subredesLAN->pluck('id'))->orderBy(DB::raw('INET_ATON(ip)'))->get();

            // Recupera todas as requisições ativas de subredes NAT ordenadas pelo IP
            $natRequestsAllowed = Requisicao::where('status', 1)->whereIn('subrede_id', $subredesNAT->pluck('id'))->orderBy(DB::raw('INET_ATON(ip)'))->get();

            // Apaga os vínculos [IP, MAC] que existiam antes
            unset($configXML->dhcpd->lan->staticmap); // lan == LAN
            unset($configXML->dhcpd->opt1->staticmap); // opt1 == NAT

            for ($i = 0; $i < 2; ++$i) {
                // Inicia o iterador
                $iterator = 0;

                // Determina qual é a subrede sendo atualizada na iteração, primeiramente é atualizado a rede LAN e, em seguinda, a rede NAT
                if ($i == 0)
                {
                    $subnet = 'lan';
                    $requestsAllowed = $lanRequestsAllowed;
                }
                else
                {
                    $subnet = 'opt1';
                    $requestsAllowed = $natRequestsAllowed;
                }

                // Para cada requisição liberada é criada uma nova entrada no static map
                foreach ($requestsAllowed as $request)
                {
                    $configXML->dhcpd->$subnet->staticmap[$iterator]->mac = $request->mac;
                    $configXML->dhcpd->$subnet->staticmap[$iterator]->ipaddr = $request->ip;
                    $configXML->dhcpd->$subnet->staticmap[$iterator]->hostname = "Requisicao-" . $request->id;
                    $configXML->dhcpd->$subnet->staticmap[$iterator]->descr = $request->usuarioNome . '-' . $request->descricao_dispositivo;
                    $configXML->dhcpd->$subnet->staticmap[$iterator]->addChild("arp_table_static_entry");
                    $configXML->dhcpd->$subnet->staticmap[$iterator]->addChild("filename");
                    $configXML->dhcpd->$subnet->staticmap[$iterator]->addChild("rootpath");
                    $configXML->dhcpd->$subnet->staticmap[$iterator]->addChild("defaultleasetime");
                    $configXML->dhcpd->$subnet->staticmap[$iterator]->addChild("maxleasetime");
                    $configXML->dhcpd->$subnet->staticmap[$iterator]->addChild("gateway");
                    $configXML->dhcpd->$subnet->staticmap[$iterator]->addChild("domain");
                    $configXML->dhcpd->$subnet->staticmap[$iterator]->addChild("domainsearchlist");
                    $configXML->dhcpd->$subnet->staticmap[$iterator]->addChild("ddnsdomain");
                    $configXML->dhcpd->$subnet->staticmap[$iterator]->addChild("ddnsdomainprimary");
                    $configXML->dhcpd->$subnet->staticmap[$iterator]->addChild("ddnsdomainkeyname");
                    $configXML->dhcpd->$subnet->staticmap[$iterator]->addChild("ddnsdomainkey");
                    $configXML->dhcpd->$subnet->staticmap[$iterator]->addChild("tftp");
                    $configXML->dhcpd->$subnet->staticmap[$iterator]->addChild("ldap");
                    ++$iterator;
                }
            }

            // Salva o arquivo de configuração, sobreescrevendo o antigo;
            return $configXML->saveXML($pathConfigFile);
        }
    }

    /**
     * Atualiza a tabela ARP do servidor ARP de uma determinada rede (LAN e NAT)
     * @return bool True se bem sucedido e falso caso contrário
     */
    public static function refreshPfsense()
    {
        $configFile = storage_path('app/config/config.xml');

        // Realiza o backup do arquivo de configuração anterior
        if (File::exists($configFile))
        {
            $lastModified = File::lastModified($configFile);
            File::copy($configFile, storage_path('app/config/config-' . $lastModified . '.xml'));
        }

        try
        {
            // Obtém o arquivo de configurações mais recente
            SSH::into("pfsense")->get("/cf/conf/config.xml", $configFile); // Path do arquivo local e path do arquivo remoto

            // Modificar o arquivo de configuração, adicionando o novo static map
            $status = PfsenseController::rebuildStaticMap($configFile);

            if(!$status) return false;
            else
            {
                // Envia o novo arquivo de configuração
                SSH::into("pfsense")->put($configFile, '/cf/conf/config.xml');

                // Remove o cache da configuração e reinicia o firewall com a nova configuração
                $commands = ["rm /tmp/config.cache", "/etc/rc.reload_all"];
                SSH::into("pfsense")->run($commands);

                event(new NewConfigurationFile());
            }
        }
        catch (\Exception $e)
        {
            logger()->error('Erro pfSense', ['mensagem' => $e->getMessage(), 'codigo' => $e->getCode()]);
            return false;
        }

        return true;
    }


    /**
     * Exporta as configurações do servidor pfSense.
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse Arquivo XML de onfiguração da rede NAT
     */
    public function exportConfig()
    {
        return response()->download(storage_path('app/config/config.xml'), 'config.xml');
    }

    /**
     * Aplica as mudanças que estão pendentes.
     * @return \Illuminate\Http\RedirectResponse Página anteiror
     */
    public function applyChanges()
    {

        if(PfsenseController::refreshPfsense())
        {
            $tipo = "success";
            $mensagem = "Mudanças aplicadas com sucesso!";
            cache()->flush();
        }
        else
        {
            $tipo = "error";
            $mensagem = "Erro ao aplicar mudanças! Verifique o log para mais detalhes.";
        }

        session()->flash('tipo', $tipo);
        session()->flash('mensagem', $mensagem);

        return back();
    }

    /**
     * Envia um magic packet para um determinado dispositivo com o intuito de ligá-lo. É necessário que o dispositivo
     * tenha a funcionalidade Wake-On-Lan ativada e devidamente configurada.
     * @param Requisicao $dispositivo Dispositivo a ser enviado o magic packet
     */
    public function wakeOnLan(Requisicao $dispositivo)
    {
        $tipo = 'info';
        $mensagem = 'Magic packet enviado para o dispositivo ' . $dispositivo->mac;

        try
        {
            SSH::into("pfsense")->run("wol " . $dispositivo->mac);
            event(new RequestWake($dispositivo, auth()->user()));
        }
        catch(\Exception $e)
        {
            $tipo = 'error';
            $mensagem = 'Erro ao enviar o magic packet. Veja o log para mais detalhes.';
            event(new RequestWakeFailed($dispositivo, auth()->user(), $e->getMessage()));
        }

        session()->flash('tipo', $tipo);
        session()->flash('mensagem', $mensagem);

        return back();
    }
}
