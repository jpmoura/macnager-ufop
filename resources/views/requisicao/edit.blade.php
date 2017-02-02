@extends('layout.base')

@section('title')
    Editar Requisição
@endsection

@section('description')
    Modifique os campos para editar a requisição.
@endsection

@section('breadcrumb')
    <li><i class="fa fa-hand-o-up"></i> Requisições</li>
    <li><i class="fa fa-edit"></i> Editar</li>
@endsection

@push('extra-css')
    {!! HTML::style('public/js/plugins/jQueryUI/jquery-ui.min.css') !!}
    {!! HTML::style('public/js/plugins/datatables/dataTables.bootstrap.css') !!}
@endpush

@push('extra-scripts')
    {!! HTML::script('public/js/plugins/datatables/jquery.dataTables.min.js') !!}
    {!! HTML::script('public/js/plugins/datatables/dataTables.bootstrap.min.js') !!}
    {!! HTML::script('public/js/plugins/jQueryMask/jquery.mask.min.js') !!}
    <script>
        $(document).ready(function() {
            $('#macAddress').mask('00:00:00:00:00:00', {'translation': {0: {pattern: /[A-Fa-f0-9]/} } } );
        });
    </script>
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
@endpush

@section('content')
    <div class='row'>
        <div class='col-lg-12'>
            <div class="box box-primary-ufop">
                <div class="box-body">
                    <form class="form" action="{{ route('updateRequisicao') }}" accept-charset="UTF-8" method="post" enctype="multipart/form-data">
                        {{ csrf_field() }}

                        <input type="hidden" name="id" value="{{ $requisicao->id }}">

                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                                <input type="text" value="{{ $requisicao->responsavelNome }}" disabled class="form-control" title="Pessoa responsável pelo usuário" required data-toggle="tooltip" data-placement="top">
                            </div>
                            <p class="help-block">
                                @if(auth()->user()->cpf == $requisicao->responsavel)
                                    Lembre-se que você será o responsável por todo e qualquer desvio de conduta por parte do usuário.
                                @else
                                    O responsável pelo usuário <span class="text-bold">não</span> pode ser alterado.
                                @endif
                            </p>
                        </div>

                        <div class="form-group">
                            <div class="input-group {{ $errors->has('usuario') ? 'has-error' : '' }}">
                                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                <input id="usuario" name="usuario" data-mask="000.000.000-00" data-mask-reverse="true" type="text" minlength="14" maxlength="14" value="{{ $errors->has('usuario') ? old('usuario') : $requisicao->usuario }}" placeholder="CPF do usuário que irá utilizar o dispositivo" class="form-control" required>
                            </div>
                            @if($errors->has('usuario'))
                                <p class="text-danger">{!! $errors->first('usuario') !!}</p>
                            @endif
                            <p class="help-block">
                                Caso o usuário não seja uma pessoa e sim todo um laboratório, por exemplo, você pode clicar
                                <a href="#" data-toggle="modal" data-target="#infoModal">aqui</a> para ver a lista de usuários padrões.
                            </p>
                        </div>

                        <div class="row">
                            <div id='userDetails' class="col-lg-12"></div>
                            <input type="hidden" name="usuarioNome" value="{{ $requisicao->usuarioNome }}">
                        </div>

                        <div class="form-group">
                            <div class="input-group {{ $errors->has('tipousuario') ? 'has-error' : '' }}">
                                <span class="input-group-addon"><i class="fa fa-users"></i></span>
                                <select name="tipousuario" class="form-control" required title="Tipo do usuário">
                                    <option value="">Selecione um tipo de usuário</option>
                                    @foreach ($usuarios as $usuario)
                                        <option value="{{$usuario->id}}" @if((old('usuario') == $usuario->id)) selected @elseif(($requisicao->tipo_usuario == $usuario->id)) selected @endif>{!! $usuario->descricao !!}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if($errors->has('tipousuario'))
                                <p class="text-danger">{!! $errors->first('tipousuario') !!}</p>
                            @endif
                            <p class="help-block">Para usuários do tipo organização, selecione o tipo <a>Recurso com Múltiplos Usuários</a></p>
                        </div>

                        <div class="form-group">
                            <div class="input-group {{ $errors->has('tipodispositivo') ? 'has-error' : '' }}">
                                <span class="input-group-addon"><i class="fa fa-laptop"></i></span>
                                <select name="tipodispositivo" class="form-control" required title="Tipo do dispositivo">
                                    <option value="">Selecione um tipo de dispositivo</option>
                                    @foreach ($dispositivos as $dispositivo)
                                        <option value="{{$dispositivo->id}}" @if((old('tipodispositivo') == $dispositivo->id)) selected @elseif($requisicao->tipo_dispositivo == $dispositivo->id) selected @endif>{!! $dispositivo->descricao !!}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if($errors->has('tipodispositivo'))
                                <p class="text-danger">{!! $errors->first('tipodispositivo') !!}</p>
                            @endif
                            <p class="help-block">Tipo do dispositivo que o usuário irá usar pra se conectar na rede da UFOP.</p>
                        </div>

                        <div class="form-group">
                            <div class="input-group {{ $errors->has('mac') ? 'has-error' : '' }}">
                                <span class="input-group-addon"><i class="fa fa-hashtag"></i></span>
                                <input id="macAddress" name="mac" type='text' class='form-control' value="{{$errors->has('mac') ? old('mac') : $requisicao->mac}}" placeholder="Endereço MAC da placa de rede" minlength="17" maxlength="17" required>
                            </div>
                            @if($errors->has('mac'))
                                <p class="text-danger">{!! $errors->first('mac') !!}</p>
                            @endif
                            <p class="help-block">Você pode encontrar o endereço MAC da placa de rede do dispositivo seguindo <a target="_blank" href="{{ route('showTermRequisicao', base64_encode('tutorial-mac.pdf')) }}">este tutorial.</a></p>
                        </div>

                        <div class="form-group">
                            <div class="input-group {{ $errors->has('descricao') ? 'has-error' : '' }}">
                                <span class="input-group-addon"><i class="fa fa-search"></i></span>
                                <input name="descricao" type='text' maxlength="100" value="{{$errors->has('descricao') ? old('descricao') : $requisicao->descricao_dispositivo}}" class='form-control' placeholder="Descrição do dispositivo" required>
                            </div>
                            @if($errors->has('descricao'))
                                <p class="text-danger">{!! $errors->first('descricao') !!}</p>
                            @endif
                            <p class="help-block">Forneça uma breve descrição do dispositivo para que ele possa ser facilmente identificado posteriormente.</p>
                        </div>

                        <div class="form-group">
                            <div class="input-group {{ $errors->has('justificativa') ? 'has-error' : '' }}">
                                <span class="input-group-addon"><i class="fa fa-comment"></i></span>
                                <textarea name="justificativa" class="form-control" style="resize: none;" placeholder="Justificativa para adição do dispositivo" maxlength="100" required>{{$errors->has('justificativa') ? old('justificativa') : $requisicao->justificativa}}</textarea>
                            </div>
                            <p class="help-block">Explique sucintamente a importância deste dispositivo ter acesso a rede da UFOP. Uma boa justificativa aumenta as chances de aprovação.</p>
                        </div>

                        <br />

                        <div class="panel {{ $errors->has('termo') ? 'panel-danger' : 'panel-info' }}">
                            <div class="panel-heading">
                                <h3 class="panel-title"><i class="fa fa-file-pdf-o"></i> Termo de Compromisso</h3>
                            </div>
                            <div class="panel-body">
                                <p>Selecione o arquivo em formato PDF que corresponde ao termo de compromisso devidamente preenchido e assinado.</p>
                                <p>O modelo do termo pode ser encontrado neste <a target="_blank" href="{{ route('showTermRequisicao', base64_encode('termos/default.pdf')) }}">link</a>.</p>
                                <p>Você pode ver o termo atual clicando <a target="_blank" href="{{ route('showTermRequisicao', base64_encode($requisicao->termo)) }}">aqui</a>.</p>
                                <input name="termo" type='file' title="Arquivo PDF do termo de compromisso assinado">
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="button" class="btn btn-ufop" onClick="history.back()"><i class='fa fa-times'></i> Cancelar</button>
                            <button type="reset" class="btn btn-default"><i class='fa fa-eraser'></i> Limpar</button>
                            <button type="submit" class="btn btn-success"><i class='fa fa-check'></i> Confirmar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div><!-- /.col -->
    </div><!-- /.row -->

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
                                <th>Código</th>
                                <th>Organização</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($organizacoes as $organizacao)
                                <tr>
                                    <td>{{ $organizacao->cpf }}</td>
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
@endsection
