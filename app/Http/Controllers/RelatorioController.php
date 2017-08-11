<?php

namespace App\Http\Controllers;

use App\Requisicao;
use App\Subrede;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Collection;

class RelatorioController extends Controller
{
    /**
     * Gera uma cor aleatória
     * @return string Representação em hexadecimal de uma cor
     */
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
        foreach ($activeIpsRaw as $rawIP)
            $activeIps[] = $rawIP->ip;

        // Verifica quais IP da subrede não estão sendo utilizados
        $unusedIPs = array();
        foreach ($allSubnetIps as $subnetIp)
            if (!in_array($subnetIp, $activeIps))
                $unusedIPs[] = $subnetIp; // Se o IP não estiver na lista de ativos, ent"ao adiciona da lista de IPs não utilizados

        return $unusedIPs;
    }

    /**
     * Gera o gráfico com as informações do uso da rede em relação aos tipos de usuário
     */
    private function generateDevicesPerUserTypeChart()
    {
        // Agrupa todos os tipos de usuários com requisições ativas
        $todosTiposUsuarios = Requisicao::with('tipoDoUsuario')->where('status', 1)->get();
        $todosTiposUsuarios = $todosTiposUsuarios->groupBy('tipoDoUsuario.descricao');

        $dataset = array();
        $cores = array();
        foreach($todosTiposUsuarios as $tipo)
        {
            $dataset[] = $tipo->count();
            $cores[] = $this->rand_color();
        }

        $chart = app()->chartjs
            ->name('grafico')
            ->type('pie')
            ->element('grafico')
            ->labels($todosTiposUsuarios->keys()->all())
            ->size(['width' => 400, 'height' => 200])
            ->datasets([
                [
                    'backgroundColor' => $cores,
                    'data' => $dataset
                ]
            ])
            ->options([
                'title' => ['display' => true, 'text' => 'Quantidades de Dipositivos por Tipo de Usuário'],
            ]);

        return $chart;
    }

    /**
     * Gera o gráfico com as informações da rede em relação aos tipos de usuário
     */
    private function generateDevicesPerTypeChart()
    {
        // Agrupa todos os tipos de usuários com requisições ativas
        $requisicoesAtivas = Requisicao::with('tipoDoDispositivo')->where('status', 1)->get();
        $requisicoesAtivas = $requisicoesAtivas->groupBy('tipoDoDispositivo.descricao');

        $dataset = array();
        $cores = array();
        foreach($requisicoesAtivas as $tipo)
        {
            $dataset[] = $tipo->count();
            $cores[] = $this->rand_color();
        }

        $chart = app()->chartjs
            ->name('grafico')
            ->type('pie')
            ->element('grafico')
            ->labels($requisicoesAtivas->keys()->all())
            ->size(['width' => 400, 'height' => 200])
            ->datasets([
                [
                    'backgroundColor' => $cores,
                    'data' => $dataset
                ]
            ])
            ->options([
                'title' => ['display' => true, 'text' => 'Quantidades de Dipositivos por Tipo'],
            ]);

        return $chart;
    }

    /**
     * Gera o gráfico das requisições ja feitas e o seu atual status
     */
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
            ->name('grafico')
            ->type('pie')
            ->element('grafico')
            ->size(['width' => 400, 'height' => 200])
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

    /**
     * Gera o gráfico de barras empilhadas sobre a utilização de cada subrede cadastrada, ou seja, a quantidade de
     * IPs livres e usados por subrede.
     */
    private function generateSubnetUsageChart()
    {
        $subredes = Subrede::all();
        $datasets = array();
        $labels = array();
        $livres = array();
        $usados = array();

        foreach($subredes as $subrede)
        {
            $ipsLivres = count($this->getAvailableIps($subrede));
            $todosIps = count($subrede->getAllIps());

            $usados[] = $todosIps - $ipsLivres;
            $livres[] = $ipsLivres;
            $labels[] = $subrede->descricao;
        }

        $datasets[] = [
            'backgroundColor' => '#b52a3e',
            'data' => $usados,
            'label' => ['Em Uso'],
        ];

        $datasets[] = [
            'backgroundColor' => '#36A2EB',
            'data' => $livres,
            'label' => ['Livre'],
        ];

        $chart = app()->chartjs
            ->name('grafico')
            ->type('bar')
            ->element('grafico')
            ->labels($labels)
            ->datasets($datasets)
            ->options([
                'title' => [
                    'display' => true,
                    'text' => 'Utilização das Subredes'
                ],
                'scales' => [
                    'xAxes' => [
                        ['stacked' => true]
                    ],
                    'yAxes' => [
                        ['stacked' => true]
                    ]
                ]
            ]);

        return $chart;
    }

    /**
     * Renderiza a página com o relatório estatístico sobre o uso de cada subrede
     */
    public function showStatisticalSubnet()
    {
        $grafico = $this->generateSubnetUsageChart();
        return view('relatorios.estatistico.subrede', compact('grafico'));
    }

    /**
     * Renderiza a página com o relatório estatístico sobre a rede por tipo de usuário
     */
    public function showStatisticalUserType()
    {
        $grafico = $this->generateDevicesPerUserTypeChart();
        return view('relatorios.estatistico.tipousuario', compact('grafico'));
    }

    /**
     * Renderiza a página com o relatório estatístico sobre a rede por tipo de dispositivo
     */
    public function showStatisticalDeviceType()
    {
        $grafico = $this->generateDevicesPerTypeChart();
        return view('relatorios.estatistico.tipodispositivo', compact('grafico'));
    }

    /**
     * Renderiza a página que contém o relatório estatístico sobre os status das requisições
     */
    public function showStatisticalRequestsStatus()
    {
        $grafico = $this->generateRequestsPerStatusChart();
        return view('relatorios.estatistico.status', compact('grafico'));
    }
}
