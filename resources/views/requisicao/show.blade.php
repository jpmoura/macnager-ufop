@extends('admin.admin_base')

@section('pedidos')
    active
@endsection

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

@section('prescripts')
    <link rel="stylesheet" href="{{asset('public/plugins/datatables/dataTables.bootstrap.css')}}">
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            @if(Session::has("tipo"))
                <div class="row">
                    <div class="text-center alert alert-dismissible @if(Session::get('tipo') == 'Sucesso') alert-success @elseif(Session::get('tipo') == 'Informação') alert-info @else alert-danger @endif" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <strong>{{Session::get("tipo")}}!</strong> {!! Session::get("mensagem") !!}
                    </div>
                </div>
            @endif

            <div class="box box-primary-ufop">
                <div class="box-header">
                    <ul class="nav nav-tabs">
                        <li role="presentation" @if($tipo == 0) class="active" @endif><a href="{{ route('showRequest', 0)}}">Em aberto @if(Session::has('novosPedidos') && Session::get('novosPedidos') > 0) <span class="label label-success">{{Session::get('novosPedidos')}}</span> @endif</a></li>
                        <li role="presentation" @if($tipo == 1) class="active" @endif><a href="{{ route('showRequest', 1)}}"><i class="fa fa-check"></i> Aprovadas</a></li>
                        <li role="presentation" @if($tipo == 2) class="active" @endif><a href="{{ route('showRequest', 2)}}"><i class="fa fa-times"></i> Negadas</a></li>
                        <li role="presentation" @if($tipo == 3) class="active" @endif><a href="{{ route('showRequest', 3)}}"><i class="fa fa-history"></i> Vencidas</a></li>
                        <li role="presentation" @if($tipo == 4) class="active" @endif><a href="{{ route('showRequest', 4)}}"><i class="fa fa-ban"></i> Suspensas</a></li>
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
                                            <a href="{{ route('detailsRequest', $requisicao->id) }}" class="btn btn-success btn-xs"><i class="fa fa-legal"></i> Avaliar</a>
                                        @else
                                            <a href="{{ route('detailsRequest', $requisicao->id) }}" class="btn btn-xs btn-ufop "><i class="fa fa-search-plus"></i> Detalhes</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr>
                            <tr>
                                <th>ID</th>
                                <th>Responsável</th>
                                <th>Usuário</th>
                                <th>Tipo do Usuário</th>
                                <th>Tipo do Dispositivo</th>
                                <th>Data de Submissão</th>
                                <th>Ação</th>
                            </tr>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('extrascripts')
    <script src="{{ asset('public/plugins/datatables/jquery.dataTables.min.js')}}"></script>
    <script src="{{ asset('public/plugins/datatables/dataTables.bootstrap.min.js')}}"></script>
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
@endsection
