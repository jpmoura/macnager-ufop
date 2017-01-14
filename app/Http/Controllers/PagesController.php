<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use App\Http\Requests;
use App\Requisicao;

class PagesController extends Controller
{
    /**
     * Renderiza a view da página inicial que contém informações diferentes de acordo com o tipo do usuário
     */
    public function home()
    {
        // Se for um administrador
        if(Auth::user()->isAdmin())
        {
            // Recupera a quantidade de dispositivos alocados em cada faixa
            for ($faixa=152; $faixa < 156; $faixa++)
            {
                $count = Requisicao::where('ip', 'like', '200.239.' . $faixa . '.%')->where('status', 1)->count();
                $used[$faixa] = $count;
            }

            // Recupera a quantidade de requisições que ainda não foram julgadas
            session()->put('novosPedidos', Requisicao::where('status', '=', 0)->count());

            return view('index')->with(['faixa' => $used]);
        }
        else
        {
            // Recupera a quantidade de cada requisição feita pelo usuário
            $accepted = Requisicao::where('status', 1)->where('responsavel', Auth::user()->cpf)->count();
            $rejected = Requisicao::where('status', 2)->where('responsavel', Auth::user()->cpf)->count();
            $outdated = Requisicao::where('status', 3)->where('responsavel', Auth::user()->cpf)->count();
            $blocked = Requisicao::where('status', 4)->where('responsavel', Auth::user()->cpf)->count();

            return view('index')->with(['aceitas' => $accepted, 'rejeitadas' => $rejected, 'vencidas' => $outdated, 'bloqueadas' => $blocked]);
        }
    }

    /**
     * Renderiza a view com informações sobre o sistema
     */
    public function about()
    {
        return view('about');
    }

    /**
     * Exporta as últimas configurações enviadas ao servidor pfSense
     */
    public function exportConfig()
    {
        return response()->download(storage_path('app/config/config.xml'), 'config.xml');
    }
}
