@extends('emails.base')

@section('image')
<img src="{{ $message->embed('public/img/email/negada.png') }}" alt="Negação">
@endsection

@section('title')
Requisição Negada
@endsection

@section('body')
    <p align="justify">
        Olá {!! explode(" ", $user->nome)[0] !!}, sua requisição de ID #{{ $request->id }} acaba de ser reprovada.
    </p>
    <p align="justify">
        Essa requisição está relacionada ao usuário {!! $request->usuarioNome !!} e a descrição que você ofereceu foi
        {!! $request->descricao_dispositivo !!}.
    </p>
    <p align="justify">
        A justificativa para a negação foi {!! $request->juizMotivo !!}. Você pode ver os detalhes da requisição clicando
        no botão abaixo.
    </p>
@endsection

@section('button-link')
request/show/{{ $request->id }}
@endsection

@section('button-title')
Ver Detalhes
@endsection
