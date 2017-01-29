@extends('layout.base')

@section('title')
    Lista de Dispositivos
@endsection

@section('description')
    {{$description or 'Essa é a lista com todos os dispositivos com acesso liberado.'}}
@endsection

@section('breadcrumb')
    <li><i class="fa fa-laptop"></i> Dispositivos</li>
    <li><i class="fa fa-th-list"></i> Listar</li>
@endsection

@push('extra-css')
    {!! HTML::style('public/js/plugins/datatables/dataTables.bootstrap.css') !!}
@endpush

@push('extra-scripts')
    {!! HTML::script('public/js/plugins/datatables/jquery.dataTables.min.js') !!}
    {!! HTML::script('public/js/plugins/datatables/dataTables.bootstrap.min.js') !!}
    <script>
        $(document).ready(function () {
            $("#usados").DataTable( {
                "bSort" : false,
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
                        <li role="presentation" @if($tipo == 1) class="active" @endif><a href="{{ route('indexDevice', 1) }}"><i class="fa fa-check"></i> Ativos</a></li>
                        <li role="presentation" @if($tipo == 3) class="active" @endif><a href="{{ route('indexDevice', 3) }}"><i class="fa fa-history"></i> Vencidos</a></li>
                        <li role="presentation" @if($tipo == 4) class="active" @endif><a href="{{ route('indexDevice', 4) }}"><i class="fa fa-ban"></i> Suspensos</a></li>
                        <li role="presentation" @if($tipo == 5) class="active" @endif><a href="{{ route('indexDevice', 5) }}"><i class="fa fa-power-off"></i> Desligados</a></li>
                    </ul>
                </div>
                <div class="box-body">
                    <div class="table">
                        <table id="usados" class="table table-bordered table-striped table-hover text-center">
                            <thead>
                            <tr>
                                <th>IP</th>
                                <th>Usuário</th>
                                <th>Dispositivo</th>
                                <th>MAC</th>
                                <th>Descrição</th>
                                <th>Aprovado em</th>
                                <th>Válido até</th>
                                @if($tipo < 5)
                                    <th>Ações</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($liberados as $registro)
                                <tr>
                                    <td>{{ $registro->ip }}</td>
                                    <td>{{ $registro->usuarioNome}}</td>
                                    <td>{{ $registro->tipodispositivo}}</td>
                                    <td>{{ $registro->mac }}</td>
                                    <td>{!! $registro->descricao_dispositivo !!}</td>
                                    <td>{{ date_format(date_create($registro->avaliacao),"d/m/Y H:i:s") }}</td>
                                    <td>
                                        @if(is_null($registro->validade))
                                            Indeterminado
                                        @else
                                            {{ date_format(date_create($registro->validade),"d/m/Y H:i:s") }}
                                        @endif
                                    </td>
                                    @if ($tipo < 5)
                                        <td>
                                            <a href="{{ route('editDevice', $registro->id) }}" class="btn btn-xs btn-ufop"><i class="fa fa-edit"></i> Editar</a>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr>
                                <th>IP</th>
                                <th>Usuário</th>
                                <th>Dispositivo</th>
                                <th>MAC</th>
                                <th>Descrição</th>
                                <th>Aprovado em</th>
                                <th>Válido até</th>
                                @if ($tipo < 5)
                                    <th>Ações</th>
                                @endif
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
