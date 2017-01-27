@extends('layout.base')

@section('dispositivo')
    active
@endsection

@section('title')
    Editar Dispositivo
@endsection

@section('description')
    Modifique os campos para editar um dispositivo da rede.
@endsection

@section('breadcrumb')
    <li><i class="fa fa-laptop"></i> Dispositivos</li>
    <li><i class="fa fa-edit"></i> Editar</li>
@endsection

@push('extra-css')
    {!! HTML::style('public/js/plugins/jQueryUI/jquery-ui.min.css') !!}
@endpush

@push('extra-scripts')
    {!! HTML::script('public/js/plugins/jQueryMask/jquery.mask.min.js') !!}
    {!! HTML::script('public/js/plugins/jQueryUI/jquery-ui.min.js') !!}
    {!! HTML::script('public/js/plugins/jQueryUI/datepicker-pt-BR.js') !!}
    <script>
        submitEditModal = function(){
            $('#loadingModal').modal({backdrop: 'static', keyboard: false});
            document.forms['editForm'].submit();
        };
        submitSuspendModal = function(){
            $('#suspendModal').modal('hide');
            $('#loadingModal').modal({backdrop: 'static', keyboard: false});
            document.forms['suspendForm'].submit();
        };
        submitDisableModal = function(){
            $('#disableModal').modal('hide');
            $('#loadingModal').modal({backdrop: 'static', keyboard: false});
            document.forms['disableForm'].submit();
        };
    </script>
    <script>
        $(document).ready(function() {
            $('#macAddress').mask('00:00:00:00:00:00', {'translation': {0: {pattern: /[A-Fa-f0-9]/} } } );
            $("#datepicker").datepicker($.datepicker.regional['pt-BR']);
        });
    </script>
    <script type="text/javascript">
        $(function(){
            $('#usuario').blur(function(){
                console.log('Fazendo requisição');
                $("#userDetails").html("<img width='36px' height='36px' alt='Carregando...' src='{{ asset('public/img/loading.gif') }}'/>"); // ícone mostrando o carregamento da informação
                $.ajax({
                    url: '{{  route('searchLdapUser') }}', // url
                    type: "post", // método
                    data: {'cpf':$('input[name=usuario]').val(), '_token': $('input[name=_token]').val()}, // dados para o método post

                    success: function(response){
                        $("#userDetails").html("<div class='panel panel-info'><div class='panel-heading'><h3 class='panel-title'>Detalhes do Usuário</h3></div><div class='panel-body text-justify'>");

                        // Se a resposta for OK
                        if(response.status == 'success') { // Achou o usuário
                            $("#userDetails").html("<div class='panel panel-info'><div class='panel-heading'><h3 class='panel-title'>Detalhes do Usuário</h3></div>" +
                                "<div class='panel-body'>" +
                                "<p><i class='fa fa-user'></i> " + response.name + "</p>" +
                                "<p><i class='fa fa-envelope'></i> " + response.email + "</p>" +
                                "<p><i class='fa fa-users'></i> " + response.group + "</p>" +
                                "</div>" +
                                "</div>");
                            $('input[name=usuarioNome]').val(response.name);
                        }
                        else { // Não encontrou ninguém
                            $("#userDetails").html("<div class='panel panel-info'><div class='panel-heading'>" +
                                "<h3 class='panel-title'>Detalhes do Responsável</h3></div><div class='panel-body'>" +
                                "<p>" + response.msg + "</p><p>É <span class='text-bold'>necessário</span> que o futuro usuário esteja cadastrado no servidor LDAP.</p>" +
                                "</div></div>");
                        }
                    },

                    // Se houver erro na requisição (e.g. 404)
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        $("#userDetails").html(XMLHttpRequest.responseText);
                    },

                    complete: function(data){
                        console.log(data);
                    }
                });
            });
        });
    </script>
    <script type="text/javascript">
        $(function(){
            $('#responsavel').blur(function(){
                console.log('Fazendo requisição');
                $("#responsibleDetails").html("<img width='36px' height='36px' alt='Carregando...' src='{{ asset('public/img/loading.gif') }}'/>"); // ícone mostrando o carregamento da informação
                $.ajax({
                    url: '{{ route('searchLdapUser') }}', // url
                    type: "post", // método
                    data: {'cpf':$('input[name=responsavel]').val(), '_token': $('input[name=_token]').val()}, // dados para o método post

                    success: function(response){

                        // Se a resposta for OK
                        if(response.status == 'success') { // Achou o usuário
                            $("#responsibleDetails").html("<div class='panel panel-info'><div class='panel-heading'><h3 class='panel-title'>Detalhes do Responsável</h3></div>" +
                                "<div class='panel-body'>" +
                                "<p><i class='fa fa-user'></i> " + response.name + "</p>" +
                                "<p><i class='fa fa-envelope'></i> " + response.email + "</p>" +
                                "<p><i class='fa fa-users'></i> " + response.group + "</p>" +
                                "</div>" +
                                "</div>");
                            $('input[name=responsavelNome]').val(response.name);
                        }
                        else { // Não encontrou ninguém
                            $("#responsibleDetails").html("<div class='panel panel-info'><div class='panel-heading'>" +
                                "<h3 class='panel-title'>Detalhes do Responsável</h3></div><div class='panel-body'>" +
                                "<p>" + response.msg + "</p><p>É <span class='text-bold'>necessário</span> que o responsável esteja cadastrado no servidor LDAP.</p>" +
                                "</div></div><br />");
                        }
                    },

                    // Se houver erro na requisição (e.g. 404)
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        $("#responsibleDetails").html(XMLHttpRequest.responseText);
                    },

                    complete: function(data){
                        console.log(data);
                    }
                });
            });
        });
    </script>
@endpush

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <form class="form" action="{{ route('editDevice') }}" method="post" id="editForm">
                {{ csrf_field() }}
                <div class="box box-primary-ufop">
                    <div class="box-body">
                        <input type="hidden" name="id" value="{{$requisicao->id}}">

                        <div class="input-group">
                            <span class="input-group-addon">IP</span>
                            <select name="ip" class="form-control" required data-toggle="tooltip" data-placement="top" title="Endereço IP destinado ao dispositvo">
                                <option value="">Selecione um IP</option>
                                <option value="{{$requisicao->ip}}" selected>{{$requisicao->ip}}</option>
                            </select>
                        </div>

                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                            <input id="responsavel" type="text" value="{!!$requisicao->responsavel!!}"  minlength="11" maxlength="11" name="responsavel" class="form-control" title="Pessoa responsável pelo usuário" required data-toggle="tooltip" data-placement="top">
                        </div>

                        <div class="row">
                            <div id='responsibleDetails' class="col-lg-12"></div>
                            <input type="hidden" name="responsavelNome" value="{{$requisicao->responsavelNome}}">
                        </div>

                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-user"></i></span>
                            <input id="usuario" name="usuario" value="{{$requisicao->usuario}}" type="text" minlength="11" maxlength="11" placeholder="CPF do usuário que irá utilizar o dispositivo" required class="form-control cpf">
                        </div>

                        <div class="row">
                            <div id='userDetails' class="col-lg-12"></div>
                            <input type="hidden" name="usuarioNome" value="{{$requisicao->usuarioNome}}">
                        </div>

                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-users"></i></span>
                            <select class="form-control" name="tipousuario" title="Tipo do usuário">
                                @foreach ($tiposusuario as $usuario)
                                    <option value="{{$usuario->id}}" @if($requisicao->tipo_usuario == $usuario->id) selected @endif>{!! $usuario->descricao !!}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-laptop"></i></span>
                            <select class="form-control" name="tipodispositivo" title="Tipo do dispositivo">
                                @foreach ($tiposdispositivo as $dispositivo)
                                    <option value="{{$dispositivo->id}}" @if($requisicao->tipo_dispositivo == $dispositivo->id) selected @endif>{!! $dispositivo->descricao !!}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-hashtag"></i></span>
                            <input id="macAddress" name="mac" type='text' class='form-control' value="{{$requisicao->mac}}" minlength="17" maxlength="17" required data-toggle="tooltip" data-placement="top" title="Endereço MAC da placa de rede">
                        </div>

                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-search"></i></span>
                            <input name="descricao" type='text' class='form-control' value="{{$requisicao->descricao_dispositivo}}" required data-toggle="tooltip" data-placement="top" title="Descrição do Dispositivo">
                        </div>

                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-history"></i></span>
                            <input id="datepicker" name="validade" type="text" @if(!is_null($requisicao->validade)) value="{{date_format(date_create($requisicao->validade),"d/m/Y")}}" @endif minlength="10" maxlength="10" class="form-control" placeholder="Validade (Deixe em branco para prazo inderteminado)">
                        </div>
                    </div>

                    <div class="box-footer text-center">
                        <button type="button" class="btn btn-ufop" onClick="history.go(-1)"><i class="fa fa-arrow-left"></i> Voltar</button>
                        @if($requisicao->status == 1)
                            <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#suspendModal"><i class="fa fa-ban"> Bloquear</i></button>
                        @elseif ($requisicao->status == 4)
                            <a href="{{ route('reactiveRequest', $requisicao->id)}}" class="btn btn-primary"><i class="fa fa-power-off"></i> Desbloqueado</a>
                        @endif
                        @if($requisicao->status < 5 && $requisicao->status != 3)
                            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#disableModal"><i class="fa fa-power-off"> Desativar</i></button>
                        @endif
                        <button type="reset" class="btn bg-gray"><i class="fa fa-eraser"></i> Resetar</button>
                        <button id="edit" type="button" onclick="submitEditModal();" class="btn btn-success"><i class="fa fa-check"></i> Aplicar</button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    @if($requisicao->status == 1)
        <div class="modal fade modal-warning" id="suspendModal" tabindex="-1" role="dialog" aria-labelledby="denyModal" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title text-center"><i class="fa fa-ban"></i> Bloquear Acesso</h4>
                    </div>
                    <form class="form" action="{{ route('suspendRequest') }}" method="post" id="suspendForm">
                        {{ csrf_field() }}

                        <input type="hidden" name="id" value="{{$requisicao->id}}">

                        <div class="modal-body">
                            <p class="text-justify">O IP será bloqueado e não poderã ser usado por nenhum outro dispositivo. Essa ação pode ser desfeita posteriormente via reativação do dispositivo.</p>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-comment"></i></span>
                                <textarea name="juizMotivo" class="form-control no-resize" placeholder="Justificativa para suspender o acesso do dispositivo." required></textarea>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><i class="fa fa-times"></i> Cancelar</button>
                            <button type="button" onclick="submitSuspendModal();" class="btn bg-black"><i class="fa fa-ban"></i> Bloquear</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade modal-danger" id="disableModal" tabindex="-1" role="dialog" aria-labelledby="disableModal" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title text-center"><i class="fa fa-power-off"></i> Desativar Dispositivo</h4>
                    </div>
                    <form class="form" action="{{ route('disableRequest') }}" method="post" id="disableForm">
                        {{ csrf_field() }}

                        <input type="hidden" name="id" value="{{ $requisicao->id }}">

                        <div class="modal-body">
                            <p class="text-justify">O dispositivo será desligado da rede, o IP ficará disponível para ser atribuído a outro dispositivo. Essa ação <span class="text-bold">não</span> pode ser desfeita.</p>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-comment"></i></span>
                                <textarea name="juizMotivo" class="form-control no-resize" placeholder="Justificativa para desativar o dispositivo." required></textarea>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><i class="fa fa-times"></i> Cancelar</button>
                            <button type="button" onclick="submitDisableModal();" class="btn bg-black"><i class="fa fa-power-off"></i> Desativar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title text-center">Aguarde</h4>
                </div>
                <div class="modal-body text-center">
                    Gerando novos arquivos ARP e DHCP e enviando para os servidores.
                    <br />
                    <br />
                    <img src="{{asset('public/img/bigloading.gif')}}" />
                </div>
            </div>
        </div>
    </div>
@endsection
