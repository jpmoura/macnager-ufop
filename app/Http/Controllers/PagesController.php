<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Requisicao;

class PagesController extends Controller
{


    /**
     * Recupera informações para serem mostradas na página inicial dependendo do nível do usuário
     * @return mixed View com dados sumarizados
     */
    public function home()
    {
        $hoje = Carbon::today();
        $proximoMes = Carbon::today()->addMonth();

        // Se for um administrador
        if(auth()->user()->isAdmin())
        {
            $requisioesPendentes = Requisicao::where('status', '=', 0)->count();

            $dispositivosAtivos = Requisicao::with('tipoDoDispositivo')->where('status', 1)->count();

            $percentagemDispositivosSemTipoConhecido = Requisicao::where('status', 1)->where('tipo_dispositivo', 1)->count() / $dispositivosAtivos * 100;
            $percentagemDispositivosSemTipoConhecido = number_format($percentagemDispositivosSemTipoConhecido, 2,',','.');

            $percentagemDispositivosSemDonoConhecido = Requisicao::where('status', 1)->where('tipo_usuario', 1)->count() / $dispositivosAtivos * 100;
            $percentagemDispositivosSemDonoConhecido = number_format($percentagemDispositivosSemDonoConhecido, 2, ',', '.');

            $requisicoesProximasDoVencimento = Requisicao::with('tipoDoUsuario', 'tipoDoDispositivo')->where('status', 1)->whereBetween('validade', [$hoje, $proximoMes])->get();

            return view("home.admin")->with([
                'requisicoesPendentes' => $requisioesPendentes,
                'dispositivosAtivos' => $dispositivosAtivos,
                'dispositivosSemTipoConhecido' => $percentagemDispositivosSemTipoConhecido,
                'dispositivosSemDonoConhecido' => $percentagemDispositivosSemDonoConhecido,
                'requisicoesProximasDoVencimento' => $requisicoesProximasDoVencimento,
            ]);
        }
        else
        {
            $cpfResponsavel = auth()->user()->cpf;

            // requisições ativas
            $requisicoesAtivas = Requisicao::where('status', 1)->where('responsavel', $cpfResponsavel)->count();

            // requisições desligadas
            $requicoesDesligadas = Requisicao::where('status', 5)->where('responsavel', $cpfResponsavel)->count();

            // requisições vencidas
            $requisicoesVencidas = Requisicao::where('status', 3)->where('responsavel', $cpfResponsavel)->count();

            // requisições bloqueadas
            $requisicoesBloqueadas = Requisicao::where('status', 4)->where('responsavel', $cpfResponsavel)->count();

            // Requisições que estão para vencer nos próximos 30 dias
            $requisicoesProximasDoVencimento = Requisicao::where('status', 1)->where('responsavel', $cpfResponsavel)->whereBetween('validade', [$hoje, $proximoMes])->get();

            // Requisições pendentes, ou seja, que ainda estão em avaliação para decidir se serão aprovadas ou não
            $requisicoesPendentes = Requisicao::with('tipoDoUsuario', 'tipoDoDispositivo')->where('status', 0)->where('responsavel', $cpfResponsavel)->get();

            // requisições a vencer
            return view('home.user')->with([
                'requisicoesAtivas' => $requisicoesAtivas,
                'requisicoesVencidas' => $requisicoesVencidas,
                'requisicoesBloqueadas' => $requisicoesBloqueadas,
                'requisicoesDesligadas' => $requicoesDesligadas,
                'requisicoesProximasDoVencimento' => $requisicoesProximasDoVencimento,
                'requisicoesPendentes' => $requisicoesPendentes,
            ]);
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
