@extends('layout.base')

@section('title')
    Detalhes da requisição #{{ $requisicao->id }}
@endsection

@section('description')
    Aqui estão os atributos da requisição bem como seus status.
@endsection

@section('breadcrumb')
    <li><i class="fa fa-hand-paper-o"></i> Requisições</li>
    <li><i class="fa fa-search"></i> Detalhes</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
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
                                <a class="btn btn-primary" target='_blank' href="{{ route('showTermRequisicao', base64_encode($requisicao->termo)) }}"><i class='fa fa-eye'></i> Visualizar termo</a>
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
                                              @elseif($requisicao->status == 4) Suspenso
                                              @else Desativado
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

                        <br />

                        {{-- Se a requisição estiver em espera --}}
                        @if($requisicao->status == 0)
                            @can('administrate') {{-- Se o usuário for administrador, mostra formulário de autenticação  --}}

                                @push('extra-css')
                                    {!! HTML::style('public/js/plugins/jQueryUI/jquery-ui.min.css') !!}
                                @endpush

                                @push('extra-scripts')
                                    {!! HTML::script('public/js/plugins/jQueryUI/jquery-ui.min.js') !!}
                                    {!! HTML::script('public/js/plugins/jQueryUI/datepicker-pt-BR.js') !!}
                                    <script>
                                        $(document).ready(function() {
                                            $( "#datepicker" ).datepicker($.datepicker.regional['pt-BR']);
                                        });

                                        submitApproval = function(){
                                            $('#loadingModal').modal({backdrop: 'static', keyboard: false});
                                            document.forms['approveForm'].submit();
                                        };
                                    </script>


                                    {{-- AJAX de recuperação de IPs não usados de acordo com a Subrede --}}
                                    <script>
                                        $(function(){
                                            $('#subrede').change(function(){
                                                console.log('Fazendo requisição');

                                                $("#ips").empty(); // Limpa as opções disponíveis

                                                $.ajax({
                                                    url: '{{ secure_url('subnet/ips') }}' + '/' + this.selectedIndex, // url
                                                    type: "get", // método

                                                    success: function(response)
                                                    {
                                                        // Se a resposta for OK
                                                        if(response.count > 0)
                                                        { // Verificar se o count  é maior que 0
                                                            $.each(response.ips, function () {
                                                                $("#ips").append('<option value="'+ this +'">'+ this +'</option>') // Cria a opção para cada IP livre
                                                            });
                                                        }
                                                        else
                                                        { // Nenhum IP está livre
                                                            $("#ips").append('<option value="">Nehum endereço IP disponível para essa subrede</option>') // Informa que n"ao existem IPs livres para a subrede
                                                        }
                                                    },

                                                    // Se houver erro na requisição (e.g. 404)
                                                    error: function (XMLHttpRequest, textStatus, errorThrown)
                                                    {
                                                        $("#ips").append('<option value="">Erro durante requisição</option>');
                                                        console.log('Error in Subnet:' + errorThrown);
                                                    },
                                                });
                                            });
                                        });
                                    </script>
                                @endpush

                                <form id="approveForm" class="form" action="{{ route('approveRequisicao') }}" method="post">

                                    {{ csrf_field() }}

                                    <input type="hidden" name="id" value="{{$requisicao->id}}">

                                    {{-- Subrede --}}
                                    <div class="form-group {{ $errors->has('subrede') ? ' has-error' : '' }}">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-sitemap"></i></span>
                                            <select id="subrede" name="subrede" class="form-control" required data-toggle="tooltip" data-placement="top" title="Suberede a qual o dispositivo fará parte">
                                                <option value="">Selecione a Subrede</option>
                                                @foreach($subredes as $subrede)
                                                    <option value="{{ $subrede->id }}">{{ $subrede->tipo->descricao }} - {!! $subrede->descricao !!}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @if($errors->has('subrede'))
                                            <p class="help-block">{!! $errors->first('subrede') !!}</p>
                                        @endif
                                    </div>

                                    {{-- Endereço IP --}}
                                    <div class="form-group {{ $errors->has('ip') ? ' has-error' : '' }}">
                                        <div class="input-group">
                                            <span class="input-group-addon">IP</span>
                                            <select id="ips" name="ip" class="form-control" required data-toggle="tooltip" data-placement="top" title="Endereço IP destinado ao dispositvo">
                                                <option value="">Selecione uma subrede para visualizar os IPs</option>
                                            </select>
                                        </div>
                                        @if($errors->has('ip'))
                                            <p class="help-block">{!! $errors->first('ip') !!}</p>
                                        @endif
                                    </div>

                                    {{-- Data de validade --}}
                                    <div class="form-group {{ $errors->has('validade') ? ' has-error' : '' }}">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-history"></i></span>
                                            <input id="datepicker" name="validade" type="text" minlength="10" maxlength="10" class="form-control" placeholder="Validade do cadastro" data-toggle="tooltip" data-placement="top" title="Validade">
                                        </div>
                                        @if($errors->has('validade'))
                                            <p class="help-block">{!! $errors->first('validade') !!}</p>
                                        @endif
                                    </div>

                                    <br />

                                    <div class="text-center">
                                        <div class="text-center">
                                            <button type="button" class="btn btn-ufop" onClick="history.back()"><i class='fa fa-arrow-left'></i> Voltar</button>
                                            <a href="{{ route('editRequisicao', $requisicao->id) }}" class="btn bg-navy"><i class="fa fa-edit"></i> Editar</a>
                                            @if($requisicao->responsavel == auth()->user()->cpf)
                                                <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#deleteModal"><i class="fa fa-trash-o"></i> Apagar</button>
                                            @endif
                                            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#denyModal"><i class='fa fa-times'></i> Negar</button>
                                            <button type="button" onclick="submitApproval()" class="btn btn-success"><i class='fa fa-check'></i> Aprovar</button>
                                        </div>
                                    </div>
                                </form>
                            @endcan
                            @cannot('administrate')
                                    {{-- O usuário dono da requisição pode editar ou apagar a requisição enquanto ela não for julgada --}}
                                    @if(auth()->user()->cpf == $requisicao->responsavel)
                                        <button type="button" class="btn btn-ufop" onClick="history.back()"><i class='fa fa-arrow-left'></i> Voltar</button>
                                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteModal"><i class="fa fa-trash-o"></i> Apagar</button>
                                        <a href="{{ route('editRequisicao', $requisicao->id)}}" class="btn bg-navy"><i class="fa fa-edit"></i> Editar</a>
                                    @endif
                            @endcannot
                        @else
                            <br />
                            <div class="text-center">
                                <button type="button" class="btn btn-ufop" onClick="history.back()"><i class='fa fa-arrow-left'></i> Voltar</button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('manipulateRequisicao', $requisicao)
        <div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title text-center">Aguarde</h4>
                    </div>
                    <div class="modal-body text-center">
                        Gerando e enviando um novo arquivo de configuração para o pfSense.
                        <br />
                        <br />
                        <img src="{{asset('public/img/bigloading.gif')}}" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal com justificativa para negar --}}
        <div class="modal fade modal-danger" id="denyModal" tabindex="-1" role="dialog" aria-labelledby="denyModal" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title text-center"><i class="fa fa-times"></i> Negar Requisição</h4>
                    </div>
                    <form class="form" action="{{ route('denyRequisicao') }}" method="post">
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
                            <button type="submit" class="btn bg-black"><i class="fa fa-gavel"></i> Negar</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade {{ auth()->user()->isAdmin() ? 'modal-warning' : 'modal-danger' }}" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
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
                        <form action="{{ route('deleteRequisicao') }}" method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="id" value="{{$requisicao->id}}">
                            <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><i class="fa fa-times"></i> Cancelar</button>
                            <button type="submit" class="btn bg-black pull-right"><i class="fa fa-trash-o"></i> Apagar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endcan
@endsection
