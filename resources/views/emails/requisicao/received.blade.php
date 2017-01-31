@extends('emails.base')

@section('image')
<img src="{{ $message->embed('public/img/email/recebida.png') }}" alt="Caixas sendo entregues">
@endsection

@section('title')
Requisição Recebida
@endsection

@section('body')
    <p align="justify">
        Olá {{ explode(" ", $user->nome)[0] }}, recebemos sua requisição e no momento ela está em processo de avaliação.
    </p>
    <p align="justify">
        Assim que houver um parecer, você receberá outro e-mail lhe informando se ela foi aprovada ou negada.
    </p>
    <p align="justify">
        Você também pode acompanhar o status da requisição na opção <a href="https://200.239.152.5/macnager/request/list/user" target="_blank">Minhas Requisições</a>
        no menu principal do <a href="https://200.239.152.5/macnager/">MACnager</a> ou clicando no botão abaixo:
    </p>
@endsection

@section('button-link')
request/show/{{ $request->id }}
@endsection

@section('button-title')
Ver Status
@endsection