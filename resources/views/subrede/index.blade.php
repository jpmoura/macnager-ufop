@extends('layout.base')

@section('title')
    Lista de Subredes
@endsection

@section('description')
    Essa é a lista com todas as subredes cadastradas até agora.
@endsection

@section('breadcrumb')
    <li><i class="fa fa-sitemap"></i> Subredes</li>
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

            @if(Session::has("tipo"))
                <div class="row">
                    <div class="text-center alert alert-dismissible @if(Session::get('tipo') == 'Sucesso') alert-success @elseif(Session::get('tipo') == 'Informação') alert-info @else alert-danger @endif" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <strong>{{Session::get("tipo")}}!</strong> {!! Session::get("mensagem") !!}
                    </div>
                </div>
            @endif

            <div class="box box-primary-ufop">
                <div class="box-body">
                    <div class="table">
                        <table id="tipos" class="table table-bordered table-striped table-hover text-center">
                            <thead>
                            <tr>
                                <th>Endereço</th>
                                <th>CIDR</th>
                                <th>Ignorar endereço inicial (gateway)</th>
                                <th>Ignorar endereço final (brodcast)</th>
                                <th>Descrição</th>
                                <th>Tipo</th>
                                <th>Ações</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($subredes as $subrede)
                                <tr>
                                    <td>{{ $subrede->endereco }}</td>
                                    <td>{{ $subrede->cidr }}</td>
                                    <td>{!! $subrede->ignorar_gateway ? "<span class='text-success'>Sim</span>" : "<span class='text-danger'>Não</span>" !!}</td>
                                    <td>{!! $subrede->ignorar_broadcast ? "<span class='text-success'>Sim</span>" : "<span class='text-danger'>Não</span>" !!}</td>
                                    <td>{!! $subrede->descricao !!}</td>
                                    <td>{{ $subrede->tipo->descricao }}</td>
                                    <td>
                                        <a href="{{ route('editSubrede', $subrede->id) }}" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i> Editar</a>
                                        <a href="{{ route('destroySubrede', $subrede->id) }}" class="btn btn-xs btn-danger"><i class="fa fa-trash-o"></i> Apagar</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr>
                                <th>Endereço</th>
                                <th>CIDR</th>
                                <th>Ignorar endereço inicial (gateway)</th>
                                <th>Ignorar endereço final (brodcast)</th>
                                <th>Descrição</th>
                                <th>Tipo</th>
                                <th>Ações</th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row text-center">
                <a class="btn btn-success" href="{{ route('createSubrede') }}"><i class="fa fa-plus"></i> Adiconar Subrede</a>
            </div>
        </div>
    </div>
@endsection
