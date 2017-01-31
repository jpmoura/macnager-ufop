@extends('layout.base')

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
    {!! HTML::style('public/js/plugins/datatables/dataTables.bootstrap.css') !!}
@endpush

@push('extra-scripts')
    {!! HTML::script('public/js/plugins/jQueryMask/jquery.mask.min.js') !!}
    {!! HTML::script('public/js/plugins/jQueryUI/jquery-ui.min.js') !!}
    {!! HTML::script('public/js/plugins/jQueryUI/datepicker-pt-BR.js') !!}
    {!! HTML::script('public/js/plugins/datatables/jquery.dataTables.min.js') !!}
    {!! HTML::script('public/js/plugins/datatables/dataTables.bootstrap.min.js') !!}

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
        submitUnlock = function(){
            $('#disableModal').modal('hide');
            $('#loadingModal').modal({backdrop: 'static', keyboard: false});
            window.location.href = "{{ route('reactiveRequisicao', $requisicao->id) }}";
        };
    </script>

    <script>
        $(document).ready(function() {
            $('#macAddress').mask('00:00:00:00:00:00', {'translation': {0: {pattern: /[A-Fa-f0-9]/} } } );
            $("#datepicker").datepicker($.datepicker.regional['pt-BR']);
            $('.cpf').mask('000.000.000-00', {reverse: true});
        });
    </script>

    {{-- Opções da tabela de organização --}}
    <script>
        $(function () {
            $("#organizacoes").DataTable( {
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

    {{-- AJAX de recuperação de IPs não usados de acordo com a Subrede --}}
    <script>
        $(function(){
            $('#subrede').change(function(){
                console.log('Fazendo requisição');

                $("#ips").empty(); // Limpa as opções disponíveis

                $.ajax({
                    url: '{{ secure_url('/subnet/ips') }}' + '/' + $('#subrede').val(), // url
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
                        $("#ips").append('<option value="">Erro durante requisição ou opção inválida selecionada</option>');
                        console.log('Error in Subnet:' + errorThrown);
                    },
                });
            });
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
            <form class="form" action="{{ route('updateDevice') }}" method="post" id="editForm">
                {{ csrf_field() }}
                <div class="box box-primary-ufop">
                    <div class="box-body">
                        <input type="hidden" name="id" value="{{ $requisicao->id }}">

                        {{-- Subrede --}}
                        <div class="form-group {{ $errors->has('subrede') ? ' has-error' : '' }}">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-sitemap"></i></span>
                                <select id="subrede" name="subrede" class="form-control" required data-toggle="tooltip" data-placement="top" title="Suberede a qual o dispositivo fará parte">
                                    <option value="" selected>Selecione a Subrede</option>
                                    @foreach($subredes as $subrede)
                                        <option value="{{ $subrede->id }}" {{ $requisicao->subrede_id == $subrede->id ? 'selected' : '' }}>{{ $subrede->tipo->descricao }} - {!! $subrede->descricao !!}</option>
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
                                    <option value="">Selecione uma subrede para visualizar todos IPs disponíveis</option>
                                    <option value="{{$requisicao->ip}}" selected>{{$requisicao->ip}}</option>
                                </select>
                            </div>
                            @if($errors->has('ip'))
                                <p class="help-block">{!! $errors->first('ip') !!}</p>
                            @endif
                        </div>

                        {{-- Responsável --}}
                        <div class="form-group {{ $errors->has('responsavel') ? ' has-error' : '' }}">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-eye"></i></span>
                                <input id="responsavel" type="text" value="{!! $errors->has('responsavel') ? old('responsavel') : $requisicao->responsavel !!}"  minlength="14" maxlength="14" name="responsavel" class="form-control cpf" title="Pessoa responsável pelo usuário" required data-toggle="tooltip" data-placement="top">
                            </div>

                            <div class="row">
                                <div id='responsibleDetails' class="col-lg-12"></div>
                                <input type="hidden" name="responsavelNome" value="{{ $errors->has('responsavelNome') ? old('responsavelNome') : $requisicao->responsavelNome }}">
                            </div>

                            @if($errors->has('responsavel'))
                                <p class="help-block">{!! $errors->first('responsavel') !!}</p>
                            @endif

                            <p class="help-block">
                                Caso o responsável não seja uma pessoa e sim todo um laboratório, por exemplo, você pode clicar
                                <a href="#" data-toggle="modal" data-target="#infoModal">aqui</a> para ver a lista de usuários-padrão (organizações).
                            </p>
                        </div>

                        {{-- Usuário --}}
                        <div class="form-group {{ $errors->has('usuario') ? ' has-error' : '' }}">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                <input id="usuario" name="usuario" value="{{ $errors->has('usuario') ? old('usuario') : $requisicao->usuario }}" type="text" minlength="14" maxlength="14" placeholder="CPF do usuário que irá utilizar o dispositivo" required class="form-control cpf">
                            </div>

                            <div class="row">
                                <div id='userDetails' class="col-lg-12"></div>
                                <input type="hidden" name="usuarioNome" value="{{ $errors->has('usuarioNome') ? old('usuarioNome') : $requisicao->usuarioNome }}">
                            </div>

                            @if($errors->has('usuario'))
                                <p class="help-block">{!! $errors->first('usuario') !!}</p>
                            @endif

                            <p class="help-block">
                                Caso o usuário não seja uma pessoa e sim todo um laboratório, por exemplo, você pode clicar
                                <a href="#" data-toggle="modal" data-target="#infoModal">aqui</a> para ver a lista de usuários-padrão (organizações).
                            </p>
                        </div>

                        {{-- Tipo do Usuário --}}
                        <div class="form-group {{ $errors->has('tipousuario') ? ' has-error' : '' }}">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-users"></i></span>
                                <select name="tipousuario" class="form-control" required title="Tipo de usuário">
                                    <option value="">Selecione um tipo de usuário</option>
                                    @foreach ($usuarios as $usuario)
                                        <option value="{{$usuario->id}}" {{ $requisicao->tipo_usuario == $usuario->id ? 'selected' : '' }}>{!! $usuario->descricao !!}</option>
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
                                        <option value="{{$dispositivo->id}}" {{ $dispositivo->id == $requisicao->tipo_dispositivo ? 'selected' : '' }}>{!! $dispositivo->descricao !!}</option>
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
                                <input id="macAddress" value="{{ $errors->has('mac') ? old('mac') : $requisicao->mac }}" name="mac" type='text' class='form-control' placeholder="Endereço MAC" minlength="17" maxlength="17" required>
                            </div>
                            @if($errors->has('mac'))
                                <p class="help-block">{!! $errors->first('mac') !!}</p>
                            @endif
                        </div>

                        {{-- Descrição do dispositivo --}}
                        <div class="form-group {{ $errors->has('descricao') ? ' has-error' : '' }}">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-search"></i></span>
                                <input name="descricao" value="{{ $errors->has('descricao') ? old('descricao') : $requisicao->descricao_dispositivo }}" type='text' class='form-control' placeholder="Descrição do dispositivo" required>
                            </div>
                            @if($errors->has('descricao'))
                                <p class="help-block">{!! $errors->first('descricao') !!}</p>
                            @endif
                        </div>

                        {{-- Justificativa --}}
                        <div class="form-group">
                            <div class="input-group {{ $errors->has('justificativa') ? ' has-error' : '' }}">
                                <span class="input-group-addon"><i class="fa fa-comment"></i></span>
                                <textarea name="justificativa" class="form-control no-resize" placeholder="Justificativa para adição do dispositivo" maxlength="100" required>{{ $errors->has('justificativa') ? old('justificativa') : $requisicao->justificativa }}</textarea>
                            </div>
                            @if($errors->has('justificativa'))
                                <p class="help-block">{!! $errors->first('justificativa') !!}</p>
                            @endif
                        </div>

                        {{-- Data de validade --}}
                        <div class="form-group {{ $errors->has('validade') ? ' has-error' : '' }}">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-history"></i></span>
                                <input id="datepicker" name="validade" value="{{ $errors->has('validade') ? date_format(date_create(old('validade')),"d/m/Y") : date_format(date_create($requisicao->validade),"d/m/Y") }}" type="text" minlength="10" maxlength="10" class="form-control" placeholder="Validade do cadastro" data-toggle="tooltip" data-placement="top" title="Validade">
                            </div>
                            @if($errors->has('validade'))
                                <p class="help-block">{!! $errors->first('validade') !!}</p>
                            @endif
                        </div>
                    </div>
                    <div class="box-footer text-center">
                        <button type="button" class="btn btn-ufop" onClick="history.go(-1)"><i class="fa fa-arrow-left"></i> Voltar</button>
                        @if($requisicao->status == 1)
                            <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#blockModal"><i class="fa fa-ban"></i> Bloquear</button>
                        @elseif ($requisicao->status == 4)
                            <button type="button" onclick="submitUnlock()" class="btn btn-primary"><i class="fa fa-unlock"></i> Desbloquear</button>
                        @endif
                        @if($requisicao->status < 5 && $requisicao->status != 3)
                            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#disableModal"><i class="fa fa-power-off"></i> Desativar</button>
                        @endif
                        <button type="reset" class="btn bg-gray"><i class="fa fa-eraser"></i> Resetar</button>
                        <button id="edit" type="button" onclick="submitEditModal();" class="btn btn-success"><i class="fa fa-check"></i> Aplicar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="infoModal" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title text-center"><i class="fa fa-th-list"></i> Lista de usuários padrão</h4>
                </div>
                <div class="modal-body">
                    <div class="table">
                        <table id="organizacoes" class='table table-striped table-condensed text-center'>
                            <thead>
                            <tr>
                                <th>CPF</th>
                                <th>Organização</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($organizacoes as $organizacao)
                                <tr>
                                    <td>{{ str_pad($organizacao->cpf, 11, "0", STR_PAD_LEFT) }}</td> {{-- Completa o número de 0 a esquerda --}}
                                    <td>{!! $organizacao->nome !!}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="text-center"><button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button></div>
                </div>
            </div>
        </div>
    </div>

    @if($requisicao->status == 1)
        <div class="modal fade modal-warning" id="blockModal" tabindex="-1" role="dialog" aria-labelledby="denyModal" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title text-center"><i class="fa fa-ban"></i> Bloquear Acesso</h4>
                    </div>
                    <form class="form" action="{{ route('blockRequisicao') }}" method="post" id="suspendForm">
                        {{ csrf_field() }}

                        <input type="hidden" name="id" value="{{$requisicao->id}}">

                        <div class="modal-body">
                            <p class="text-justify">O IP será bloqueado e não poderá ser usado por nenhum outro dispositivo. Essa ação pode ser desfeita posteriormente via reativação do dispositivo.</p>
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
                    <form class="form" action="{{ route('disableRequisicao') }}" method="post" id="disableForm">
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
                    Gerando e enviando um novo arquivo de configuração para o pfSense.
                    <br />
                    <br />
                    <img src="{{asset('public/img/bigloading.gif')}}" />
                </div>
            </div>
        </div>
    </div>
@endsection
