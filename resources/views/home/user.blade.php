@extends('home.base')

@section('content')
    <div class="row">
        {{-- Dispositivos pendentes --}}
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-green">
                <div class="inner">
                    <h3>{{ $requisicoesAtivas }}</h3>

                    @php
                        $sufixos = ['ão', ''];
                        if($requisicoesAtivas > 1)
                        {
                            $sufixos[0] = 'ões';
                            $sufixos[1] = 's';
                        }
                    @endphp

                    <p>Requisiç{{ $sufixos[0] }} ativa{{ $sufixos[1] }} atualmente</p>
                </div>
                <div class="icon">
                    <i class="fa fa-check-circle"></i>
                </div>
                <a href="{{ route('indexUserRequisicao') }}" class="small-box-footer">
                    Ver requisições <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        {{-- Quantidade de dispositivos ativos --}}
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-gray">
                <div class="inner">
                    <h3>{{ $requisicoesVencidas }}</h3>

                    @php
                        $sufixos = ['ão', ''];
                        if($requisicoesVencidas > 1)
                        {
                            $sufixos[0] = 'ões';
                            $sufixos[1] = 's';
                        }
                    @endphp

                    <p>Requisiç{{ $sufixos[0] }} vencida{{ $sufixos[1] }}</p>
                </div>
                <div class="icon">
                    <i class="fa fa-history"></i>
                </div>
                <a href="{{ route('indexUserRequisicao') }}" class="small-box-footer">
                    Ver requisições <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        {{-- Porcentagem de dispositivos desconhecidos --}}
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3>{{ $requisicoesBloqueadas }}</h3>

                    @php
                        $sufixos = ['ão', ''];
                        if($requisicoesBloqueadas > 1)
                        {
                            $sufixos[0] = 'ões';
                            $sufixos[1] = 's';
                        }
                    @endphp

                    <p>Requisic{{ $sufixos[0] }} bloqueada{{ $sufixos[1] }} temporariamente</p>
                </div>
                <div class="icon">
                    <i class="fa fa-ban"></i>
                </div>
                <a href="{{ route('indexUserRequisicao') }}" class="small-box-footer">
                    Ver requisições <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        {{-- Quantidade de dispositivos com responsável desconhecido --}}
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-red">
                <div class="inner">
                    <h3>{{ $requisicoesDesligadas }}</h3>

                    @php
                        $sufixos = ['ão', ''];
                        if($requisicoesDesligadas > 1)
                        {
                            $sufixos[0] = 'ões';
                            $sufixos[1] = 's';
                        }
                    @endphp

                    <p>Requisiç{{ $sufixos[0] }} desativada{{ $sufixos[1] }} em definitivo</p>
                </div>
                <div class="icon">
                    <i class="fa fa-times"></i>
                </div>
                <a href="{{ route('indexUserRequisicao') }}" class="small-box-footer">
                    Ver requisições <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="box box-primary-ufop">
                <div class="box-header">
                    <h3 class="box-title">
                        {{ $requisicoesPendentes->count() }} requisiç{{ $requisicoesPendentes->count() > 1 ? 'ões' : 'ão' }} aguardando avaliação
                    </h3>
                </div>
                <div class="box-body">
                    <div class="table">
                        <table class="table table-bordered table-striped table-hover table-condensed text-center datatable">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Usuário</th>
                                <th>Tipo do Usuário</th>
                                <th>Tipo do Dispositivo</th>
                                <th>Data de Submissão</th>
                                <th>Ação</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($requisicoesPendentes as $requisicao)
                                <tr>
                                    <td>{{ $requisicao->id }}</td>
                                    <td>{!! $requisicao->usuarioNome !!}</td>
                                    <td>{!! $requisicao->tipoDoUsuario->descricao !!}</td>
                                    <td>{!! $requisicao->tipoDoDispositivo->descricao !!}</td>
                                    <td>{!! date_format(date_create($requisicao->submissao), "d/m/Y H:i:s") !!}</td>
                                    <td><a href="{{ route('editRequisicao', $requisicao->id) }}" class="btn btn-xs btn-ufop"><i class="fa fa-edit"></i> Editar</a></td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr>
                                <th>ID</th>
                                <th>Usuário</th>
                                <th>Tipo do Usuário</th>
                                <th>Tipo do Dispositivo</th>
                                <th>Data de Submissão</th>
                                <th>Ação</th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="box box-primary-ufop">
                <div class="box-header">
                    <h3 class="box-title">
                        {{ $requisicoesProximasDoVencimento->count() }} requisiç{{ $requisicoesProximasDoVencimento->count() > 1 ? 'ões' : 'ão' }} com validade a vencer nos próximos 30 dias
                    </h3>
                </div>
                <div class="box-body">
                    <div class="table">
                        <table class="table table-bordered table-striped table-hover table-condensed text-center datatable">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Usuário</th>
                                <th>Tipo do Usuário</th>
                                <th>Tipo do Dispositivo</th>
                                <th>Data de Vencimento</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($requisicoesProximasDoVencimento as $requisicao)
                                <tr>
                                    <td>{{ $requisicao->id }}</td>
                                    <td>{!! $requisicao->usuarioNome !!}</td>
                                    <td>{!! $requisicao->tipoDoUsuario->descricao !!}</td>
                                    <td>{!! $requisicao->tipoDoDispositivo->descricao !!}</td>
                                    <td>{!! date_format(date_create($requisicao->validade),"d/m/Y H:i:s") !!}</td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr>
                                <th>ID</th>
                                <th>Usuário</th>
                                <th>Tipo do Usuário</th>
                                <th>Tipo do Dispositivo</th>
                                <th>Data de Vencimento</th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
