@extends('home.base')

@section('content')
    <div class="row">
        {{-- Dispositivos pendentes --}}
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-green">
                <div class="inner">
                    <h3>{{ $requisicoesPendentes }}</h3>

                    <p>Pedidos aguardando avaliação</p>
                </div>
                <div class="icon">
                    <i class="fa fa-gavel"></i>
                </div>
                <a href="{{ route('indexAllRequisicao', 0) }}" class="small-box-footer">
                    Ver pedidos <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        {{-- Quantidade de dispositivos ativos --}}
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-blue">
                <div class="inner">
                    <h3>{{ $dispositivosAtivos }}</h3>

                    <p>Dispositivos ativos na rede</p>
                </div>
                <div class="icon">
                    <i class="fa fa-arrow-up"></i>
                </div>
                <a href="{{ route('indexDevice', 1) }}" class="small-box-footer">
                    Ver dispositivos <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        {{-- Porcentagem de dispositivos desconhecidos --}}
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3>{{ $dispositivosSemTipoConhecido }}%</h3>

                    <p>Dispositivos sem tipo conhecido (legado)</p>
                </div>
                <div class="icon">
                    <i class="fa fa-question-circle-o"></i>
                </div>
                <a href="{{ route('indexDevice', 1) }}" class="small-box-footer">
                    Ver dispositivos <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        {{-- Quantidade de dispositivos com responsável desconhecido --}}
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-red">
                <div class="inner">
                    <h3>{{ $dispositivosSemDonoConhecido }}%</h3>

                    <p>Dispositivos sem responsável conhecido (legado)</p>
                </div>
                <div class="icon">
                    <i class="fa fa-question-circle-o"></i>
                </div>
                <a href="{{ route('indexDevice', 1) }}" class="small-box-footer">
                    Ver dispositivos <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="box box-primary-ufop">
                <div class="box-header">
                    <h3 class="box-title">
                        {{ $requisicoesProximasDoVencimento->count() }} dispositivo{{ $requisicoesProximasDoVencimento->count() > 1 ? 's' : '' }} com validade a vencer nos próximos 30 dias
                    </h3>
                </div>
                <div class="box-body">
                    <div class="table">
                        <table id="tipos" class="table table-bordered table-striped table-hover table-condensed text-center">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Responsável</th>
                                <th>Usuário</th>
                                <th>Tipo do Usuário</th>
                                <th>Tipo do Dispositivo</th>
                                <th>Data de Vencimento</th>
                                <th>Ação</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($requisicoesProximasDoVencimento as $requisicao)
                                <tr>
                                    <td>{{ $requisicao->id }}</td>
                                    <td>{!! $requisicao->responsavelNome !!}</td>
                                    <td>{!! $requisicao->usuarioNome !!}</td>
                                    <td>{!! $requisicao->tipoDoUsuario->descricao !!}</td>
                                    <td>{!! $requisicao->tipoDoDispositivo->descricao !!}</td>
                                    <td>{!! date_format(date_create($requisicao->validade),"d/m/Y H:i:s") !!}</td>
                                    <td><a href="{{ route('editDevice', $requisicao->id) }}" class="btn btn-xs btn-ufop"><i class="fa fa-edit"></i> Editar</a></td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr>
                                <th>ID</th>
                                <th>Responsável</th>
                                <th>Usuário</th>
                                <th>Tipo do Usuário</th>
                                <th>Tipo do Dispositivo</th>
                                <th>Data de Vencimento</th>
                                <th>Ação</th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
