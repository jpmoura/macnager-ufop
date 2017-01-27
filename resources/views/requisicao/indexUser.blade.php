@extends('layout.base')

@section('title')
    Lista de Requisições
@endsection

@section('description')
    Essa é a lista com todas as requisições feitar por você até hoje.
@endsection

@section('breadcrumb')
    <li><i class="fa fa-hand-paper-o"></i> Requisições</li>
    <li><i class="fa fa-th-list"></i> Minhas Requisições</li>
@endsection

@push('extra-css')
    {!! HTML::style('public/js/plugins/datatables/dataTables.bootstrap.css') !!}
@endpush

@push('extra-scripts')
    {!! HTML::script('public/js/plugins/datatables/jquery.dataTables.min.js') !!}
    {!! HTML::script('public/js/plugins/datatables/dataTables.bootstrap.min.js') !!}
    <script>
        $(function () {
            $("#requisicoes").DataTable( {
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
                        <table id="requisicoes" class="table table-bordered table-striped table-hover text-center">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Usuário</th>
                                <th>Tipo do Usuário</th>
                                <th>Tipo do Dispositivo</th>
                                <th>Data de Submissão</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($requisicoes as $requisicao)
                                <tr>
                                    <td>{{ $requisicao->id }}</td>
                                    <td>{{ $requisicao->usuarioNome }}</td>
                                    <td>{{ $requisicao->tipoDoDispositivo->descricao }}</td>
                                    <td>{{ $requisicao->tipoDoUsuario->descricao }}</td>
                                    <td>{{ date_format(date_create($requisicao->submissao),"d/m/Y H:i:s") }}</td>
                                    <td>
                                        <span class="text-bold
                                            @if($requisicao->status == 0)
                                                text-info"><i class="fa fa-spinner fa-pulse fa-fw" aria-hidden="true"></i> Em Avaliação
                                            @elseif($requisicao->status == 1)
                                                text-success"><i class="fa fa-check"></i> Aprovado
                                            @elseif($requisicao->status == 2)
                                                text-danger"><i class="fa fa-times"></i> Negado
                                            @elseif($requisicao->status == 3)
                                                text-danger"><i class="fa fa-history"></i> Vencido
                                            @elseif($requisicao->status == 4)
                                                text-warning"><i class="fa fa-ban"></i> Bloqueado
                                            @elseif($requisicao->status == 5)
                                                text-danger"><i class="fa fa-power-off"></i> Desativado
                                            @endif
                                        </span>
                                    </td>
                                    <td><a href="{{ route('showRequest', $requisicao->id)}}" class="btn btn-xs btn-ufop"><i class="fa fa-search"></i> Detalhes</a></td>
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
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
