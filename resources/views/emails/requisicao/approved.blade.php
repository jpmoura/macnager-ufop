@extends('emails.base')

@section('image')
<img src="{{ $message->embed('public/img/email/aprovada.png') }}" alt="Sinal de verificado">
@endsection

@section('title')
Requisição Aprovada
@endsection

@section('body')
    <p align="justify">
        Olá {!! explode(" ", $user->nome)[0] !!}, sua requisição de ID #{{ $request->id }} acaba de ser aprovada e o
        dispositivo já deve ser capaz de se conectar normalmente na rede interna do campus.
    </p>
    <p align="justify">
        Essa requisição está relacionada ao usuário {!! $request->usuarioNome !!} e a descrição que você ofereceu foi
        {!! $request->descricao_dispositivo !!}.
    </p>
    <p align="justify">
        Para qualquer rede Wi-fi que se inicia com a letra do bloco e é seguida por um traço e um númeral de três
        algarismos (rede A200, por exemplo), a senha é igual a ufop0609 (em minúsculo) caso seja um dispositivo com
        placa de rede sem fio.
    </p>
@endsection

@section('button-link')
request/details/{{ $request->id }}
@endsection

@section('button-title')
Ver Detalhes
@endsection
