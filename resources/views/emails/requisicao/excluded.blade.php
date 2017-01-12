@extends('emails.base')

@section('image')
<img src="{{ $message->embed('public/img/email/excluida.png') }}" alt="Botão de desligar">
@endsection

@section('title')
Requisição Excluída
@endsection

@section('body')
    <p align="justify">
        Olá {!! explode(" ", $user->nome)[0] !!}, o dispositivo vinculado a sua requisição de ID #{{ $request->id }} acaba
        de ser desligado da rede do campus.
    </p>
    <p align="justify">
        Essa requisição está relacionada ao usuário {!! $request->usuarioNome !!} e a descrição que você ofereceu foi
        {!! $request->descricao_dispositivo !!}.
    </p>
    <p align="justify">
        A justificativa para o desligamento foi {!! $request->juizMotivo !!}. Você pode ver os detalhes da requisição
        clicando no botão abaixo.
    </p>
@endsection

@section('button-link')
request/details/{{ $request->id }}
@endsection

@section('button-title')
Ver Detalhes
@endsection