@extends('admin.admin_base')

@section('dispositivo')
    active
@endsection

@section('addMac')
    active
@endsection

@section('extracss')
    <link href="{{ asset("public/plugins/jQueryUI/jquery-ui.min.css")}}" rel="stylesheet" type="text/css" />
@endsection

@section('title')
    Adicionar Dispositivo
@endsection

@section('description')
    Complete os campos para adicionar um novo dispositivo a rede.
@endsection

@section('breadcrumb')
    <li><i class="fa fa-laptop"></i> Dispositivos</li>
    <li><i class="fa fa-plus"></i> Adicionar</li>
@endsection

@section('content')
    <div class='row'>
        <div class='col-lg-12'>

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
                    <form class="form" action="{{url('/addMac')}}" accept-charset="UTF-8" method="post" id="addmac">
                        {{ csrf_field() }}

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
                            <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                            <input id="responsavel" type="text" minlength="11" maxlength="11" name="responsavel" class="form-control" title="Pessoa responsável pelo usuário" required data-toggle="tooltip" data-placement="top">
                        </div>

                        <div class="row">
                            <div id='responsibleDetails' class="col-lg-12"></div>
                            <input type="hidden" name="responsavelNome" value="">
                        </div>

                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-user"></i></span>
                            <input id="usuario" name="usuario" type="text" minlength="11" maxlength="11" placeholder="CPF do usuário que irá utilizar o dispositivo" required class="form-control"required>
                        </div>

                        <div class="row">
                            <div id='userDetails' class="col-lg-12"></div>
                            <input type="hidden" name="usuarioNome" value="">
                        </div>


                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-users"></i></span>
                            <select name="tipousuario" class="form-control" required>
                                <option value="">Selecione um tipo de usuário</option>
                                @foreach ($usuarios as $usuario)
                                    <option value="{{$usuario->id}}">{!! $usuario->descricao !!}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-laptop"></i></span>
                            <select name="tipodispositivo" class="form-control" required>
                                <option value="">Selecione um tipo de dispositivo</option>
                                @foreach ($dispositivos as $dispositivo)
                                    <option value="{{$dispositivo->id}}">{!! $dispositivo->descricao !!}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-hashtag"></i></span>
                            <input id="macAddress" name="mac" type='text' class='form-control' placeholder="Endereço MAC" minlength="17" maxlength="17" required>
                        </div>

                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-search"></i></span>
                            <input name="descricao" type='text' class='form-control' placeholder="Descrição do dispositivo" required>
                        </div>

                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-comment"></i></span>
                            <textarea name="justificativa" class="form-control no-resize" placeholder="Justificativa para adição do dispositivo" maxlength="100" required></textarea>
                        </div>

                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-history"></i></span>
                            <input id="datepicker" name="validade" type="text" minlength="10" maxlength="10" class="form-control" placeholder="Validade do cadastro" data-toggle="tooltip" data-placement="top" title="Validade">
                        </div>

                        <br />

                        <div class="text-center">
                            <button type="button" class="btn btn-warning" onClick="history.back()">Cancelar <i class='fa fa-times'></i></button>
                            <button type="reset" class="btn btn-info">Limpar <i class='fa fa-eraser'></i></button>
                            <button type="button" onclick="submitModal();" class="btn btn-success">Confirmar <i class='fa fa-check'></i></button>
                            <button type="button" onclick="submitModal();" class="btn btn-success">Teste <i class='fa fa-check'></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div><!-- /.col -->
    </div><!-- /.row -->

    <!-- Modal -->
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

@section('extrascripts')
    <script src="{{ asset ('public/plugins/jQueryMask/jquery.mask.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset ('public/plugins/jQueryUI/jquery-ui.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset ('public/plugins/jQueryUI/datepicker-pt-BR.js') }}" type="text/javascript"></script>
    <script>
        submitModal = function(){
            $('#loadingModal').modal({backdrop: 'static', keyboard: false});
            document.forms['addmac'].submit();
        }
    </script>
    <script>
        $(document).ready(function() {
            $('#macAddress').mask('00:00:00:00:00:00', {'translation': {0: {pattern: /[A-Fa-f0-9]/} } } );
            $( "#datepicker" ).datepicker($.datepicker.regional['pt-BR']);
        });
    </script>
    <script type="text/javascript">
        $(function(){
            $('#usuario').blur(function(){
                console.log('Fazendo requisição');
                $("#userDetails").html("<img width='36px' height='36px' alt='Carregando...' src='{{ asset('img/loading.gif') }}'/>"); // ícone mostrando o carregamento da informação
                $.ajax({
                    url: '{{url('/searchperson')}}', // url
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
                            $('input[name=usuarioNome').val(response.name);
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
                $("#responsibleDetails").html("<img width='36px' height='36px' alt='Carregando...' src='{{ asset('img/loading.gif') }}'/>"); // ícone mostrando o carregamento da informação
                $.ajax({
                    url: '{{url('/searchperson')}}', // url
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
                            $('input[name=responsavelNome').val(response.name);
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
@endsection
