@extends('emails.base')

@section('image')
<img src="{{ $message->embed('public/img/email/reativada.png') }}" alt="Plug de tomada">
@endsection

@section('title')
    Requisição Reativada
@endsection

@section('body')
    <p align="justify">
        Olá {!! explode(" ", $user->nome)[0] !!}, sua requisição de ID #{{ $request->id }} acaba de ser reativada e o
        dispositivo já deve ser capaz de se conectar normalmente na rede interna do campus.
    </p>
    <p align="justify">
        Essa requisição está relacionada ao usuário {!! $request->usuarioNome !!} e a descrição que você ofereceu foi
        {!! $request->descricao_dispositivo !!}.
    </p>
@endsection

@section('button-link')
request/details/{{ $request->id }}
@endsection

@section('button-title')
Ver Detalhes
@endsection