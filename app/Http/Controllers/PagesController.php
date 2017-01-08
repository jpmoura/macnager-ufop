<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use App\Http\Requests;
use App\Requisicao;

class PagesController extends Controller
{
    public function home()
    {
        // Se for um administrador
        if(Auth::user()->nivel == 1)
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
            $accepted = Requisicao::where('status', 1)->where('responsavel', Session::get('id'))->count();
            $rejected = Requisicao::where('status', 2)->where('responsavel', Session::get('id'))->count();
            $oudated = Requisicao::where('status', 3)->where('responsavel', Session::get('id'))->count();
            $blocked = Requisicao::where('status', 4)->where('responsavel', Session::get('id'))->count();

            return view('index')->with(['aceitas' => $accepted, 'rejeitadas' => $rejected, 'vencidas' => $oudated, 'bloqueadas' => $blocked]);
        }
    }

    public function about()
    {
        return view('about');
    }

    public function exportArp()
    {
        return response()->download(storage_path('app/public/temp_arp'), 'arp_icea');
    }

    public function exportDhcpd()
    {

    }
}
