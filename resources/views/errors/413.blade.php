@extends('layout.base')

@section('title')
    Erro 413
@endsection

@section('content')
    <div class="row">
        <div class="error-page">
            <h2 class="headline text-yellow">413</h2>
            <br />
            <div class="error-content">
                <h3><i class="fa fa-warning text-yellow"></i> Entidade muito extensa!</h3>

                <p>
                    O servidor de rede considerou que o fluxo de dados HTTP enviado pela sua requisiçãp era
                    simplesmente muito extenso, ou seja, com excesso de bytes.
                </p>
                <p>
                    Isso pode ser um problema do seu navegador, que pode estar inserindo informações desnecessárias
                    na requisição. Experimente remover os cookies relacionados a este servidor.
                </p>
            </div>
        </div>
    </div>
@endsection
