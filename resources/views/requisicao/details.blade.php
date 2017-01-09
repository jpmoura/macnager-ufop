@extends('layout.base')

@section('title')
    Detalhes do pedido #{{ $requisicao->id }}
@endsection

@section('description')
    Aqui estão os atributos da requisição bem como seus status.
@endsection

@section('breadcrumb')
    <li><i class="fa fa-legal"></i> Pedidos</li>
    <li><i class="fa fa-search"></i> Detalhes</li>
@endsection

@section('prescripts')
    <link rel="stylesheet" href="{{asset('public/plugins/datatables/dataTables.bootstrap.css')}}">
    <link href="{{ asset("public/plugins/jQueryUI/jquery-ui.min.css")}}" rel="stylesheet" type="text/css" />
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

            <div class="row">

                <div class="box box-primary-ufop">
                    <div class="box-body">
                        <form class="form">

                            <h3 class="text-center"><i class="fa fa-th-list"></i> Atributos da requisição</h3>

                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                                <input type="text" class="form-control" value="{{$requisicao->responsavelNome}} ( CPF: {{$requisicao->responsavel}})" readonly data-toggle="tooltip" data-placement="top" title="Responsável">
                            </div>

                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                <input type="text" class="form-control" value="{{$requisicao->usuarioNome}} (CPF: {{$requisicao->usuario}})" readonly data-toggle="tooltip" data-placement="top" title="Usuário">
                            </div>

                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-users"></i></span>
                                <input type="text" class="form-control" value="{{$requisicao->tipoDoUsuario['descricao']}}" readonly data-toggle="tooltip" data-placement="top" title="Tipo do usuário">
                            </div>

                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-laptop"></i></span>
                                <input type="text" class="form-control" value="{{$requisicao->tipoDoDispositivo['descricao']}}" readonly data-toggle="tooltip" data-placement="top" title="Tipo do dispositivo">
                            </div>

                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-search"></i></span>
                                <input type="text" class="form-control" value="{{$requisicao->descricao_dispositivo}}" readonly data-toggle="tooltip" data-placement="top" title="Descrição do dispositivo">
                            </div>

                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-hashtag"></i></span>
                                <input type="text" class="form-control" value="{{$requisicao->mac}}" readonly data-toggle="tooltip" data-placement="top" title="Endereço MAC">
                            </div>

                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-comment"></i></span>
                                <textarea class="form-control" style="resize: none" readonly data-toggle="tooltip" data-placement="top" title="Justificativa da requisição">{{$requisicao->justificativa}}</textarea>
                            </div>

                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-share"></i></span>
                                <input type="text" class="form-control" value="{{date_format(date_create($requisicao->submissao),"d/m/Y H:i:s")}}" readonly data-toggle="tooltip" data-placement="top" title="Data de submissão da requisição">
                            </div>

                            <br />

                            <div class="text-center">
                                <a class="btn btn-primary" target='_blank' href="{{ route('showTermRequest', base64_encode($requisicao->termo)) }}"><i class='fa fa-eye'></i> Visualizar termo</a>
                            </div>

                            <br />
                            <hr>

                            <h3 class="text-center"><i class="fa fa-gavel"></i> Status de avaliação</h3>

                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-line-chart"></i></span>
                                <input type="text" class="form-control"
                                       value="@if($requisicao->status == 0) Em andamento
                                              @elseif($requisicao->status == 1) Aprovado
                                              @elseif($requisicao->status == 2) Negado
                                              @elseif($requisicao->status == 3) Vencido
                                              @else Suspenso
                                              @endif" readonly data-toggle="tooltip" data-placement="top" title="Status da requisição">
                            </div>

                            {{-- Se ela não estiver em espera --}}
                            @if($requisicao->status != 0)

                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-gavel"></i></span>
                                    <input type="text" class="form-control" value="{{date_format(date_create($requisicao->avaliacao),"d/m/Y H:i:s")}}" readonly data-toggle="tooltip" data-placement="top" title="Data da avaliação">
                                </div>

                                {{-- Se a requisição for diferente de aprovada ou em espera, mostra o motivo --}}
                                @if($requisicao->status > 1)
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-comment"></i></span>
                                        <textarea class="form-control no-resize" readonly data-toggle="tooltip" data-placement="top" title="Motivo">{!! $requisicao->juizMotivo !!}</textarea>
                                    </div>
                                {{-- Se ela tiver sido aprovada, mostra do IP vinculado ao dispositivo --}}
                                @else
                                    <div class="input-group">
                                        <span class="input-group-addon">IP</span>
                                        <input type="text" class="form-control" value="{{$requisicao->ip}}" readonly data-toggle="tooltip" data-placement="top" title="IP delegado">
                                    </div>
                                @endif
                            @endif
                        </form>

                        {{-- Se a requisição estiver em espera --}}
                        @if($requisicao->status == 0)
                            @if(Auth::user()->isAdmin()) {{-- Se o usuário for administrador, mostra formulário de autenticação  --}}
                                <form class="form" action="{{ route('approveRequest') }}" method="post">

                                    {{ csrf_field() }}

                                    <input type="hidden" name="id" value="{{$requisicao->id}}">

                                    <div class="input-group">
                                        <span class="input-group-addon">IP</span>
                                        <select name="ip" class="form-control" required data-toggle="tooltip" data-placement="top" title="Endereço IP destinado ao dispositvo">
                                            <option value="">Selecione um IP</option>
                                            @foreach ($ipsLivre as $ip)
                                                <option value="{{$ip}}">{{$ip}}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-history"></i></span>
                                        <input id="datepicker" name="validade" type="text" minlength="10" maxlength="10" class="form-control" placeholder="Validade do cadastro" data-toggle="tooltip" data-placement="top" title="Validade">
                                    </div>

                                    <br />

                                    <div class="text-center">
                                        <div class="text-center">
                                            <button type="button" class="btn btn-ufop" onClick="history.back()"><i class='fa fa-arrow-left'></i> Voltar</button>
                                            <a href="{{ route('showEditRequest', $requisicao->id) }}" class="btn bg-navy"><i class="fa fa-edit"></i> Editar</a>
                                            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#denyModal"><i class='fa fa-times'></i> Negar</button>
                                            <button type="submit" class="btn btn-success"><i class='fa fa-check'></i> Aprovar</button>
                                        </div>
                                    </div>
                                </form>
                            @endif
                        @endif
                        <br />
                        <div class="text-center">
                            <button type="button" class="btn btn-ufop" onClick="history.back()"><i class='fa fa-arrow-left'></i> Voltar</button>
                            {{-- O usuário dono da requisição pode editar ou apagar a requisição enquanto ela não for julgada --}}
                            @if($requisicao->status == 0 && $Auth::user()->cpf == $requisicao->responsavel)
                                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteModal"><i class="fa fa-trash-o"></i> Apagar</button>
                                <a href="{{ route('showEditRequest', $requisicao->id)}}" class="btn bg-navy"><i class="fa fa-edit"></i> Editar</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($requisicao->status == 0 && Auth::user()->isAdmin())
        {{-- Modal com justificativa para negar --}}
        <div class="modal fade modal-danger" id="denyModal" tabindex="-1" role="dialog" aria-labelledby="denyModal" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title text-center"><i class="fa fa-times"></i> Negar Requisição</h4>
                    </div>
                    <form class="form" action="{{ route('denyRequest') }}" method="post">
                        {{ csrf_field() }}

                        <input type="hidden" name="id" value="{{$requisicao->id}}">

                        <div class="modal-body">
                            <p class="text-center">Essa ação <span class="text-bold">NÃO</span> pode ser desfeita.</p>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-comment"></i></span>
                                <textarea name="juizMotivo" class="form-control no-resize" name="juizMotivo" placeholder="Justificativa para negar a requisição." required></textarea>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><i class="fa fa-times"></i> Cancelar</button>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-gavel"></i> Negar</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    @endif

    @if($requisicao->status == 0 && Auth::user()->cpf == $requisicao->responsavel)
        <div class="modal fade modal-danger" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title text-center"><i class="fa fa-trash-o"></i> Apagar Requisição</h4>
                    </div>
                    <div class="modal-body">
                        <p class="text-center">Essa ação <span class="text-bold">NÃO</span> pode ser desfeita</p>
                    </div>
                    <div class="modal-footer">
                        <form action="{{ route('deleteRequest') }}" method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="id" value="{{$requisicao->id}}">
                            <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><i class="fa fa-times"></i> Cancelar</button>
                            <button type="submit" class="btn btn-primary pull-right"><i class="fa fa-trash-o"></i> Apagar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('extrascripts')
    <script src="{{ asset('public/plugins/datatables/jquery.dataTables.min.js')}}"></script>
    <script src="{{ asset('public/plugins/datatables/dataTables.bootstrap.min.js')}}"></script>
    <script src="{{ asset ('public/plugins/jQueryMask/jquery.mask.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset ('public/plugins/jQueryUI/jquery-ui.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset ('public/plugins/jQueryUI/datepicker-pt-BR.js') }}" type="text/javascript"></script>
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

    <script>
        $(document).ready(function() {
            $('#macAddress').mask('00:00:00:00:00:00', {'translation': {0: {pattern: /[A-Fa-f0-9]/} } } );
            $( "#datepicker" ).datepicker($.datepicker.regional['pt-BR']);
        });
    </script>
@endsection
