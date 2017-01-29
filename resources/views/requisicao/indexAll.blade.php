@extends('layout.base')

@section('title')
    Lista de Requisições
@endsection

@section('description')
    Essa é a lista com todas as requisições feitas até hoje.
@endsection

@section('breadcrumb')
    <li><i class="fa fa-legal"></i> Pedidos</li>
    <li><i class="fa fa-th-list"></i> Todos Pedidos</li>
@endsection

@push('extra-css')
    {!! HTML::style('public/js/plugins/datatables/dataTables.bootstrap.css') !!}
@endpush

@push('extra-scripts')
    {!! HTML::script('public/js/plugins/datatables/jquery.dataTables.min.js') !!}
    {!! HTML::script('public/js/plugins/datatables/dataTables.bootstrap.min.js') !!}
    <script>
        $(function () {
            $("#tipos").DataTable( {
                "language": {
                    "lengthMenu": "Mostrar _MENU_ registros por página",
                    "zeroRecords": "Nada encontrado.",
                    "info": "Mostrando página _PAGE_ de _PAGES_",
                    "infoEmpty": "Nenhum registro disponível",
                    "infoFiltered": "(Filtrado de _MAX_ registros)",
                    "search": "Procurar:",
                    "paginate": {
                        "next": "Próximo",
                        "previous": "Anterior"
                    }
                },
                "autoWidth" : true,
                "aLengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Tudo"]]
            });
        });
    </script>
@endpush

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="box box-primary-ufop">
                <div class="box-header">
                    <ul class="nav nav-tabs">
                        <li role="presentation" @if($tipo == 0) class="active" @endif><a href="{{ route('indexAllRequisicao', 0)}}">Em aberto @if(session()->has('novosPedidos') && session('novosPedidos') > 0) <span class="label label-success">{{session('novosPedidos')}}</span> @endif</a></li>
                        <li role="presentation" @if($tipo == 1) class="active" @endif><a href="{{ route('indexAllRequisicao', 1)}}"><i class="fa fa-check"></i> Aprovadas</a></li>
                        <li role="presentation" @if($tipo == 2) class="active" @endif><a href="{{ route('indexAllRequisicao', 2)}}"><i class="fa fa-times"></i> Negadas</a></li>
                        <li role="presentation" @if($tipo == 3) class="active" @endif><a href="{{ route('indexAllRequisicao', 3)}}"><i class="fa fa-history"></i> Vencidas</a></li>
                        <li role="presentation" @if($tipo == 4) class="active" @endif><a href="{{ route('indexAllRequisicao', 4)}}"><i class="fa fa-ban"></i> Suspensas</a></li>
                        <li role="presentation" @if($tipo == 5) class="active" @endif><a href="{{ route('indexAllRequisicao', 5)}}"><i class="fa fa-power-off"></i> Desativadas</a></li>
                    </ul>
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
                                <th>Data de Submissão</th>
                                <th>Ação</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($requisicoes as $requisicao)
                                <tr>
                                    <td>{{ $requisicao->id }}</td>
                                    <td>{!! $requisicao->responsavelNome !!}</td>
                                    <td>{!! $requisicao->usuarioNome !!}</td>
                                    <td>{{ $requisicao->tipousuario }}</td>
                                    <td>{{ $requisicao->tipodispositivo }}</td>
                                    <td>{{ date_format(date_create($requisicao->submissao),"d/m/Y H:i:s") }}</td>
                                    <td>
                                        @if($tipo == 0)
                                            <a href="{{ route('showRequisicao', $requisicao->id) }}" class="btn btn-success btn-xs"><i class="fa fa-legal"></i> Avaliar</a>
                                        @else
                                            <a href="{{ route('showRequisicao', $requisicao->id) }}" class="btn btn-xs btn-ufop "><i class="fa fa-search-plus"></i> Detalhes</a>
                                        @endif
                                    </td>
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
                                <th>Data de Submissão</th>
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
