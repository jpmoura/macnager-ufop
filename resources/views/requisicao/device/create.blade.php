@extends('layout.base')

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

@push('extra-css')
    {!! HTML::style('public/js/plugins/jQueryUI/jquery-ui.min.css') !!}
@endpush

@push('extra-scripts')
    {!! HTML::script('public/js/plugins/jQueryMask/jquery.mask.min.js') !!}
    {!! HTML::script('public/js/plugins/jQueryUI/jquery-ui.min.js') !!}
    {!! HTML::script('public/js/plugins/jQueryUI/datepicker-pt-BR.js') !!}

    {{-- Modal de loading --}}
    <script>
        submitModal = function(){
            $('#loadingModal').modal({backdrop: 'static', keyboard: false});
            document.forms['addmac'].submit();
        }
    </script>

    {{-- Máscara de Endereço MAC e selecionador de datas em português --}}
    <script>
        $(document).ready(function() {
            $('#macAddress').mask('00:00:00:00:00:00', {'translation': {0: {pattern: /[A-Fa-f0-9]/} }} );
            $('.cpf').mask('000.000.000-00', {reverse: true});
            $("#datepicker").datepicker($.datepicker.regional['pt-BR']);
        });
    </script>

    {{-- AJAX de recuperação de IPs não usados de acordo com a Subrede --}}
    <script>
        $(function(){
            $('#subrede').change(function(){
                console.log('Fazendo requisição');

                $("#ips").empty(); // Limpa as opções disponíveis

                $.ajax({
                    url: '{{ url('test') }}' + '/' + this.selectedIndex, // url
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

    {{-- AJAX de pesquisa dos dados do responsável --}}
    <script type="text/javascript">
        $(function(){
            $('#usuario').blur(function(){
                console.log('Fazendo requisição');
                $("#userDetails").html("<img width='36px' height='36px' alt='Carregando...' src='{{ asset('public/img/loading.gif') }}'/>"); // ícone mostrando o carregamento da informação
                $.ajax({
                    url: '{{ route('searchLdapUser') }}', // url
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

    {{-- AJAX de pesquisa dos dados do usuário--}}
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
                                "<div class='panel-body text-left'>" +
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
                    <form class="form" action="{{ route('addDevice') }}" accept-charset="UTF-8" method="post" id="addmac">
                        {{ csrf_field() }}

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

                        {{-- Responsável --}}
                        <div class="form-group {{ $errors->has('responsavel') ? ' has-error' : '' }}">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                                <input id="responsavel" type="text" minlength="14" maxlength="14" name="responsavel" class="form-control cpf" title="Pessoa responsável pelo usuário" required data-toggle="tooltip" data-placement="top">
                            </div>

                            <div class="row">
                                <div id='responsibleDetails' class="col-lg-12"></div>
                                <input type="hidden" name="responsavelNome" value="">
                            </div>

                            @if($errors->has('responsavel'))
                                <p class="help-block">{!! $errors->first('responsavel') !!}</p>
                            @endif
                        </div>

                        {{-- Usuário --}}
                        <div class="form-group {{ $errors->has('usuario') ? ' has-error' : '' }}">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                <input id="usuario" name="usuario" type="text" minlength="14" maxlength="14" placeholder="CPF do usuário que irá utilizar o dispositivo" required class="form-control cpf">
                            </div>

                            <div class="row">
                                <div id='userDetails' class="col-lg-12"></div>
                                <input type="hidden" name="usuarioNome" value="">
                            </div>

                            @if($errors->has('usuario'))
                                <p class="help-block">{!! $errors->first('usuario') !!}</p>
                            @endif
                        </div>

                        {{-- Tipo do Usuário --}}
                        <div class="form-group {{ $errors->has('tipousuario') ? ' has-error' : '' }}">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-users"></i></span>
                                <select name="tipousuario" class="form-control" required title="Tipo de usuário">
                                    <option value="">Selecione um tipo de usuário</option>
                                    @foreach ($usuarios as $usuario)
                                        <option value="{{$usuario->id}}">{!! $usuario->descricao !!}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if($errors->has('tipousuario'))
                                <p class="help-block">{!! $errors->first('tipousuario') !!}</p>
                            @endif
                        </div>

                        {{-- Tipo do Dispositivo --}}
                        <div class="form-group {{ $errors->has('tipodispositivo') ? ' has-error' : '' }}">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-laptop"></i></span>
                                <select name="tipodispositivo" class="form-control" required title="Tipo do Dispositivo">
                                    <option value="">Selecione um tipo de dispositivo</option>
                                    @foreach ($dispositivos as $dispositivo)
                                        <option value="{{$dispositivo->id}}">{!! $dispositivo->descricao !!}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if($errors->has('tipodispositivo'))
                                <p class="help-block">{!! $errors->first('tipodispositivo') !!}</p>
                            @endif
                        </div>

                        {{-- Endereço MAC --}}
                        <div class="form-group {{ $errors->has('mac') ? ' has-error' : '' }}">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-hashtag"></i></span>
                                <input id="macAddress" name="mac" type='text' class='form-control' placeholder="Endereço MAC" minlength="17" maxlength="17" required>
                            </div>
                            @if($errors->has('mac'))
                                <p class="help-block">{!! $errors->first('mac') !!}</p>
                            @endif
                        </div>

                        {{-- Descrição do dispositivo --}}
                        <div class="form-group {{ $errors->has('descricao') ? ' has-error' : '' }}">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-search"></i></span>
                                <input name="descricao" type='text' class='form-control' placeholder="Descrição do dispositivo" required>
                            </div>
                            @if($errors->has('descricao'))
                                <p class="help-block">{!! $errors->first('descricao') !!}</p>
                            @endif
                        </div>

                        {{-- Justificativa --}}
                        <div class="form-group">
                            <div class="input-group {{ $errors->has('justificativa') ? ' has-error' : '' }}">
                                <span class="input-group-addon"><i class="fa fa-comment"></i></span>
                                <textarea name="justificativa" class="form-control no-resize" placeholder="Justificativa para adição do dispositivo" maxlength="100" required></textarea>
                            </div>
                            @if($errors->has('justificativa'))
                                <p class="help-block">{!! $errors->first('justificativa') !!}</p>
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
                            <button type="button" class="btn btn-warning" onClick="history.back()">Cancelar <i class='fa fa-times'></i></button>
                            <button type="reset" class="btn btn-info">Limpar <i class='fa fa-eraser'></i></button>
                            <button type="button" onclick="submitModal();" class="btn btn-success">Confirmar <i class='fa fa-check'></i></button>
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
                    <img src="{{ secure_asset('public/img/bigloading.gif') }}" />
                </div>
            </div>
        </div>
    </div>
@endsection