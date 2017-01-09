@extends('layout.base')

@section('usuarios')
    active
@endsection

@section('listUsers')
    active
@endsection

@section('title')
    Lista de Usuários @if($id < 4) ativos @else inativos @endif
@endsection

@section('description')
    Essa é a lista com todos os usuários
    @if($id < 4) ativos
        @if($id == 1)
            hoje
        @elseif($id == 2)
            essa semana
        @else
            esse mês
        @endif
    @else
        inativos a um mês ou mais
    @endif
@endsection

@section('breadcrumb')
    <li><i class="fa fa-users"></i> Usuários</li>
    <li><i class="fa fa-th-list"></i> Lista de Usuários Frenquentes</li>
@endsection

@push('extra-css')
    {!! HTML::style('public/js/plugins/datatables/dataTables.bootstrap.css') !!}
@endpush

@push('extra-scripts')
    {!! HTML::script('public/js/plugins/datatables/jquery.dataTables.min.js') !!}
    {!! HTML::script('public/js/plugins/datatables/dataTables.bootstrap.min.js') !!}
    <script>
        $(function () {
            $("#usuarios").DataTable( {
                "bSort" : false,
                "processing": true,
                "language": {
                    "lengthMenu": "Mostrar _MENU_ registros por página",
                    "processing": "Carregando...",
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
        // $('#usuarios')
        //   .on( 'processing.dt', function ( e, settings, processing ) {
        //       $('#processingIndicator').css( 'display', processing ? 'block' : 'none' );
        //   } )
        //   .dataTable();
    </script>
@endpush

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
                        <li role="presentation" @if($id == 1) class="active" @endif><a href="{{ route('showUsageRequest', 1) }}"><i class="fa fa-calendar-check-o"></i> Hoje</a></li>
                        <li role="presentation" @if($id == 2) class="active" @endif><a href="{{ route('showUsageRequest', 2) }}"><i class="fa fa-calendar-minus-o"></i> Semana</a></li>
                        <li role="presentation" @if($id == 3) class="active" @endif><a href="{{ route('showUsageRequest', 3) }}"><i class="fa fa-calendar"></i> Mês</a></li>
                        <li role="presentation" @if($id == 4) class="active" @endif><a href="{{ route('showUsageRequest', 4) }}"><i class="fa fa-history"></i> Inativos</a></li>
                    </ul>
                </div>
                <div class="box-body">
                    <div class="table">
                        <table id="usuarios" class="table table-bordered table-striped table-hover text-center">
                            <thead>
                            <tr>
                                <th>IP</th>
                                <th>Responsável</th>
                                <th>Usuário</th>
                                <th>Descrição do dispositivo</th>
                                @if($id < 4)
                                    <th>Enviado</th>
                                    <th>Recebido</th>
                                    <th>Total</th>
                                @endif
                                <th>Ações</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(!is_null($usuarios))
                                @foreach($usuarios as $usuario)
                                    <tr>
                                        <td>{{ $usuario->ip }}</td>
                                        <td>{!! $usuario->responsavelNome !!}</td>
                                        <td>{!! $usuario->usuarioNome !!}</td>
                                        <td>{!! $usuario->descricao_dispositivo !!}</td>
                                        @if($id < 4)
                                            <td>{{ $usuario->sent }}</td>
                                            <td>{{ $usuario->received }}</td>
                                            <td>{{ $usuario->totalTransferred }}</td>
                                        @endif
                                        <td><a href="{{ route("showEditDevice", $usuario->id) }}" class="btn btn-xs btn-default bg-ufop"><i class="fa fa-edit"></i> Editar</a></td>
                                    </tr>
                                @endforeach
                            @endif
                            </tbody>
                            <tfoot>
                            <tr>
                                <th>IP</th>
                                <th>Responsável</th>
                                <th>Usuário</th>
                                <th>Descrição do dispositivo</th>
                                @if($id < 4)
                                    <th>Enviado</th>
                                    <th>Recebido</th>
                                    <th>Total</th>
                                @endif
                                <th>Ações</th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{--<div id="processingIndicator">--}}
    {{--</div>--}}
@endsection
