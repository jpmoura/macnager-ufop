@extends('layout.base')

@section('dispositivo')
    active
@endsection

@section('tipodispositivo')
    active
@endsection

@section('listDeviceType')
    active
@endsection

@section('title')
    Lista de Tipos de Dispositivos
@endsection

@section('description')
    Essa é a lista com todos os tipos de dispostivos cadastrados até agora.
@endsection

@section('breadcrumb')
    <li><i class="fa fa-laptop"></i> Dispositivo</li>
    <li><i class="fa fa-puzzle-piece"></i> Tipo</li>
    <li><i class="fa fa-th-list"></i> Listar</li>
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
                <div class="box-body">
                    <div class="table">
                        <table id="tipos" class="table table-bordered table-striped table-hover text-center">
                            <thead>
                            <tr>
                                <th>Descrição</th>
                                <th>Ações</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($tipos as $tipo)
                                <tr>
                                    <td>{{ $tipo->descricao }}</td>
                                    <td>
                                        <a href="{{ route('editTipoDispositivo', $tipo->id) }}" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i> Editar</a>
                                        <a href="{{ route('deleteTipoDispositivo', $tipo->id) }}" class="btn btn-xs btn-danger"><i class="fa fa-trash-o"></i> Apagar</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr>
                                <th>Descrição</th>
                                <th>Ações</th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row text-center">
                <a class="btn btn-success" href="{{ route('createTipoDispositivo') }}"><i class="fa fa-plus"></i> Adiconar tipo</a>
            </div>
        </div>
    </div>
@endsection
