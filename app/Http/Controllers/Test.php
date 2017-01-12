<?php

namespace App\Http\Controllers;

use App\Mail\RequestApproved;
use App\Mail\RequestDenied;
use App\Mail\RequestExcluded;
use App\Mail\RequestReactivated;
use App\Mail\RequestReceived;
use App\Mail\RequestSuspended;
use App\Requisicao;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class Test extends Controller
{
    public function send()
    {
        $request = Requisicao::find(215);

        // Requisição recebida
        Mail::to(Auth::user()->email)->queue(new RequestReceived(Auth::user(), $request));

        // Requisição aprovada
        Mail::to(Auth::user()->email)->queue(new RequestApproved(Auth::user(), $request));

        // Requisição negada
        Mail::to(Auth::user()->email)->queue(new RequestDenied(Auth::user(), $request));

        // Requisição suspensa
        Mail::to(Auth::user()->email)->queue(new RequestSuspended(Auth::user(), $request));

        // Requisição reativada
        Mail::to(Auth::user()->email)->queue(new RequestReactivated(Auth::user(), $request));

        // Requisição excluida
        Mail::to(Auth::user()->email)->queue(new RequestExcluded(Auth::user(), $request));

        return;
    }
}
