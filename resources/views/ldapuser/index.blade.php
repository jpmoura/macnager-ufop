@extends('layout.base')

@section('title')
    Lista de Tipos de Dispositivos
@endsection

@section('description')
    Essa é a lista com todos os usuários cadastrados até agora.
@endsection

@section('breadcrumb')
    <li><i class="fa fa-laptop"></i> Usuários</li>
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
        $("#table").DataTable( {
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
                        <table id="table" class="table table-bordered table-striped table-hover text-center">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>E-mail</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($usuarios as $usuario)
                                <tr>
                                    <td>{{ $usuario->id }}</td>
                                    <td>{!! $usuario->nome !!}</td>
                                    <td>{!! $usuario->email !!}</td>
                                    <td>
                                        <span class="text-bold
                                        @if($usuario->status)
                                            text-success">Ativo
                                        @else
                                            text-danger">Inativo
                                        @endif
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('ldapuser.edit', $usuario->id) }}" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i> Editar</a>
                                        <a href="#" class="btn btn-xs btn-danger" onclick="event.preventDefault(); document.getElementById('delete-form-{{ $usuario->id }}').submit();"><i class="fa fa-trash-o"></i> Apagar</a>
                                        <form id="delete-form-{!! $usuario->id !!}" method="post" action="{{ route('ldapuser.destroy', $usuario->id) }}" style="display: none">{{ csrf_field() }} {{ method_field('DELETE') }}</form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>E-mail</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="box-footer text-center">
                    <a class="btn btn-success" role="button" href="{{ route('ldapuser.create') }}"><i
                                class="fa fa-user-plus"></i> Adicionar usuário</a>
                </div>
            </div>
        </div>
    </div>
@endsection
