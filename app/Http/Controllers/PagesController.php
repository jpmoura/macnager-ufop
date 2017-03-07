<?php

namespace App\Http\Controllers;

use App\Subrede;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Requisicao;

class PagesController extends Controller
{
    private function rand_color() {
        return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Recupera todos os IPs livres de uma subrede.
     * @param Subrede $subrede Instância da subrede que terá os IPs recuperados
     * @return array Array contendo todos os IPs
     */
    public function getAvailableIps(Subrede $subrede)
    {

        $allSubnetIps = $subrede->getAllIps(); // Obtém todos os IPs da subrede
        $activeIpsRaw = Requisicao::select('ip')->where('status', 1)->orWhere('status', 4)->get(); // Obtém todos os IPs sendo usados

        // Transforma a coleção resultante da query em um array contendo somento os IPs
        $activeIps = array();
        foreach ($activeIpsRaw as $rawIP) $activeIps[] = $rawIP->ip;

        // Verifica quais IP da subrede não estão sendo utilizados
        $unusedIPs = array();
        foreach ($allSubnetIps as $subnetIp) {
            if (!in_array($subnetIp, $activeIps)) $unusedIPs[] = $subnetIp; // Se o IP não estiver na lista de ativos, ent"ao adiciona da lista de IPs não utilizados
        }

        return $unusedIPs;
    }

    private function generateDevicesPerUserTypeChart($requisicoesAtivas)
    {
        // Agrupa todos os tipos de usuários com requisições ativas
        $todosTiposUsuarios = $requisicoesAtivas->groupBy('tipoDoUsuario.descricao');

        $tipoUsuario = array();
        $tipoUsuarioCores = array();
        foreach($todosTiposUsuarios->keys() as $key)
        {
            $tipoUsuario[$key] = $todosTiposUsuarios[$key]->count();
            $tipoUsuarioCores[] = $this->rand_color();
        }

        $chart = app()->chartjs
            ->name('devicesPerUserType')
            ->type('pie')
            ->element('devicesPerUserType')
            ->labels(array_keys($tipoUsuario))
            ->datasets([
                [
                    'backgroundColor' => $tipoUsuarioCores,
                    'data' => array_values($tipoUsuario)
                ]
            ])
            ->options([
                'title' => ['display' => true, 'text' => 'Quantidades de Dipositivos por Tipo de Usuário'],
            ]);

        return $chart;
    }

    private function generateDevicesPerTypeChart($requisicoesAtivas)
    {
        // Agrupa todos os tipos de usuários com requisições ativas
        $todosTipos = $requisicoesAtivas->groupBy('tipoDoDispositivo.descricao');

        $tipos = array();
        $tiposCores = array();
        foreach($todosTipos->keys() as $key)
        {
            $tipos[$key] = $todosTipos[$key]->count();
            $tiposCores[] = $this->rand_color();
        }

        $chart = app()->chartjs
            ->name('devicesPerType')
            ->type('pie')
            ->element('devicesPerType')
            ->labels(array_keys($tipos))
            ->datasets([
                [
                    'backgroundColor' => $tiposCores,
                    'data' => array_values($tipos)
                ]
            ])
            ->options([
                'title' => ['display' => true, 'text' => 'Quantidades de Dipositivos por Tipo'],
            ]);

        return $chart;
    }

    private function generateRequestsPerStatusChart()
    {
        $requisicoes = Requisicao::with('tipoStatus')->get();

        // Agrupa as requisicoes por tipo
        $todosTipos = $requisicoes->groupBy('tipoStatus.descricao');

        $tipos = array();
        $tiposCores = array();
        foreach($todosTipos->keys() as $key)
        {
            $tipos[$key] = $todosTipos[$key]->count();
            $tiposCores[] = $this->rand_color();
        }

        $chart = app()->chartjs
            ->name('requestsPerStatus')
            ->type('pie')
            ->element('requestsPerStatus')
            ->labels(array_keys($tipos))
            ->datasets([
                [
                    'backgroundColor' => $tiposCores,
                    'data' => array_values($tipos)
                ]
            ])
            ->options([
                'title' => ['display' => true, 'text' => 'Quantidades de Requisições por Status'],
            ]);

        return $chart;
    }


    private function generateSubnetUsageChart(Subrede $subrede)
    {
        $ipsLivres = count($this->getAvailableIps($subrede));
        $todosIps = count($subrede->getAllIps());

        $chart = app()->chartjs
            ->name('subrede' . $subrede->id)
            ->type('pie')
            ->element('subrede' . $subrede->id)
            ->labels(['Livre', 'Em Uso'])
            ->datasets([
                [
                    'backgroundColor' => ['#36A2EB', '#b52a3e'],
                    'data' => [$ipsLivres, $todosIps - $ipsLivres]
                ]
            ])
            ->options([
                'title' => ['display' => true, 'text' => 'Utilização Rede ' . $subrede->tipo->descricao . ' - ' . $subrede->descricao],
            ]);

        return $chart;
    }

    /**
     * Recupera informações para serem mostradas na página inicial dependendo do nível do usuário
     * @return mixed View com dados sumarizados
     */
    public function home()
    {
        // Se for um administrador
        if(auth()->user()->isAdmin())
        {
            $requisicoesAtivas = Requisicao::with('tipoDoDispositivo', 'tipoDoUsuario')->where('status', 1)->get();

            $charts['devicesPerUserType'] = $this->generateDevicesPerUserTypeChart($requisicoesAtivas);
            $charts['devicesPerType'] = $this->generateDevicesPerTypeChart($requisicoesAtivas);
            $charts['requestsPerStatus'] = $this->generateRequestsPerStatusChart();

            // Gerando gráficos das subredes
            $subredes = Subrede::with('tipo')->get();
            foreach($subredes as $subrede) $charts['subrede' . $subrede->id] = $this->generateSubnetUsageChart($subrede);

            // Recupera a quantidade de requisições que ainda não foram julgadas
            session()->put('novosPedidos', Requisicao::where('status', '=', 0)->count());

            return view('index', compact('charts'))->with(['subredes' => $subredes]);
        }
        else
        {
            // Recupera a quantidade de cada requisição feita pelo usuário
            $accepted = Requisicao::where('status', 1)->where('responsavel', auth()->user()->cpf)->count();
            $rejected = Requisicao::where('status', 2)->where('responsavel', auth()->user()->cpf)->count();
            $outdated = Requisicao::where('status', 3)->where('responsavel', auth()->user()->cpf)->count();
            $blocked = Requisicao::where('status', 4)->where('responsavel', auth()->user()->cpf)->count();
            // TODO fazer pare as requisições desativadas

            return view('index')->with(['aceitas' => $accepted, 'rejeitadas' => $rejected, 'vencidas' => $outdated, 'bloqueadas' => $blocked]);
        }
    }

    /**
     * Renderiza a view que contém informação sobre o sistema
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View View com informações sobre o sistema
     */
    public function about()
    {
        return view('about');
    }
}
