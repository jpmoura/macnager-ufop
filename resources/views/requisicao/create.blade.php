@extends('layout.base')

@section('title')
    Nova Requisição
@endsection

@section('description')
    Complete os campos para requerer que um dispostivo seja adicionado na rede da UFOP.
@endsection

@section('breadcrumb')
    <li><i class="fa fa-laptop"></i> Requisições</li>
    <li><i class="fa fa-plus"></i> Adicionar</li>
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
            $('.cpf').mask('000.000.000-00', {reverse: true});
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
                                "<h3 class='panel-title'>Detalhes do Usuário</h3></div><div class='panel-body'>" +
                                "<p>" + response.msg + "</p><p>É <span class='text-bold'>necessário</span> que o futuro usuário esteja cadastrado no servidor LDAP.</p>" +
                                "</div></div>");
                        }
                    },

                    // Se houver erro na requisição (e.g. 404)
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        $("#userDetails").html(XMLHttpRequest.responseText);
                    }
                });
            });
        });
    </script>
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
@endpush

@section('content')
    <div class='row'>
        <div class='col-lg-12'>
            <div class="box box-primary-ufop">
                <div class="box-body">
                    <h4 class="text-center text-bold">Todos os campos são obrigatórios</h4>

                    <br />

                    <form class="form" action="{{ route('storeRequisicao') }}" accept-charset="UTF-8" method="post" enctype="multipart/form-data">
                        {{ csrf_field() }}

                        <input type="hidden" name="responsavel" value="{!! auth()->user()->cpf !!}">
                        <input type="hidden" name="responsavelNome" value="{!! auth()->user()->nome !!}">

                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-eye"></i></span>
                                <input type="text" value="{{ auth()->user()->nome }}" disabled class="form-control" title="Pessoa responsável pelo usuário" required data-toggle="tooltip" data-placement="top">
                            </div>
                            <p class="help-block">Lembre-se que você será o responsável por todo e qualquer desvio de conduta por parte do usuário.</p>
                        </div>

                        <div class="form-group">
                            <div class="input-group {{ $errors->has('usuario') ? ' has-error' : '' }}">
                                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                <input id="usuario" name="usuario" type="text" minlength="14" maxlength="14" value="{{old('usuario')}}" placeholder="CPF do usuário que irá utilizar o dispositivo" required class="form-control cpf">
                            </div>
                            @if($errors->has('usuario'))
                                <p class="text-danger">{!! $errors->first('usuario') !!}</p>
                            @endif
                            <p class="help-block">
                                Caso o usuário não seja uma pessoa e sim todo um laboratório, por exemplo, você pode clicar
                                <a href="#" data-toggle="modal" data-target="#infoModal">aqui</a> para ver a lista de usuários-padrão (organizações).
                            </p>
                        </div>

                        <div class="row">
                            <div id='userDetails' class="col-lg-12"></div>
                            <input type="hidden" name="usuarioNome" value="{{old('usuarioNome')}}">
                        </div>

                        <div class="form-group">
                            <div class="input-group {{ $errors->has('tipousuario') ? ' has-error' : '' }}">
                                <span class="input-group-addon"><i class="fa fa-users"></i></span>
                                <select name="tipousuario" class="form-control" title="Tipo do usuário" required>
                                    <option value="">Selecione um tipo de usuário</option>
                                    @foreach ($usuarios as $usuario)
                                        <option value="{{$usuario->id}}" @if(old('tipousuario') == $usuario->id) selected @endif>{!! $usuario->descricao !!}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if($errors->has('tipousuario'))
                                <p class="text-danger">{!! $errors->first('tipousuario') !!}</p>
                            @endif
                            <p class="help-block">Para usuários que seja organizações, selecione o tipo <a>Recurso com múltiplos usuários.</a></p>
                        </div>

                        <div class="form-group">
                            <div class="input-group {{ $errors->has('tipodispositivo') ? ' has-error' : '' }}">
                                <span class="input-group-addon"><i class="fa fa-laptop"></i></span>
                                <select name="tipodispositivo" class="form-control" title="Tipo do dispositivo" required>
                                    <option value="">Selecione um tipo de dispositivo</option>
                                    @foreach ($dispositivos as $dispositivo)
                                        <option value="{{$dispositivo->id}}" @if (old('tipodispositivo') == $dispositivo->id) selected @endif>{!! $dispositivo->descricao !!}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if($errors->has('tipodispositivo'))
                                <p class="text-danger">{!! $errors->first('tipodispositivo') !!}</p>
                            @endif
                            <p class="help-block">Tipo do dispositivo que o usuário irá usar pra se conectar na rede da UFOP.</p>
                        </div>

                        <div class="form-group">
                            <div class="input-group {{ $errors->has('mac') ? ' has-error' : '' }}">
                                <span class="input-group-addon"><i class="fa fa-hashtag"></i></span>
                                <input id="macAddress" name="mac" type='text' class='form-control' value="{{old('mac')}}" placeholder="Endereço MAC da placa de rede" minlength="17" maxlength="17" required>
                            </div>
                            @if($errors->has('mac'))
                                <p class="text-danger">{!! $errors->first('mac') !!}</p>
                            @endif
                            <p class="help-block">Você pode encontrar o endereço MAC da placa de rede do dispositivo seguindo <a target="_blank" href="{{ route('showTermRequisicao', base64_encode('tutorial-mac.pdf')) }}">este tutorial.</a></p>
                        </div>

                        <div class="form-group">
                            <div class="input-group {{ $errors->has('descricao') ? ' has-error' : '' }}">
                                <span class="input-group-addon"><i class="fa fa-search"></i></span>
                                <input name="descricao" type='text' value="{{old('descricao')}}" class='form-control' placeholder="Descrição do dispositivo" required>
                            </div>
                            @if($errors->has('descricao'))
                                <p class="text-danger">{!! $errors->first('descricao') !!}</p>
                            @endif
                            <p class="help-block">Forneça uma breve descrição do dispositivo para que ele possa ser facilmente identificado no futuro.</p>
                        </div>

                        <div class="form-group">
                            <div class="input-group {{ $errors->has('justificativa') ? ' has-error' : '' }}">
                                <span class="input-group-addon"><i class="fa fa-comment"></i></span>
                                <textarea name="justificativa" class="form-control" style="resize: none;" placeholder="Justificativa para adição do dispositivo" maxlength="100" required>{{old('justificativa')}}</textarea>
                            </div>
                            @if($errors->has('justificativa'))
                                <p class="text-danger">{!! $errors->first('justificativa') !!}</p>
                            @endif
                            <p class="help-block">Explique sucintamente a importância deste dispositivo ter acesso a rede da UFOP. Uma boa justificativa aumenta as chances de aprovação.</p>
                        </div>

                        <br />

                        <div class="panel {{ $errors->has('termo') ? ' panel-danger' : 'panel-info' }}">
                            <div class="panel-heading">
                                <h3 class="panel-title"><i class="fa fa-file-pdf-o"></i> Termo de Compromisso</h3>
                            </div>
                            <div class="panel-body">
                                <p>Selecione o arquivo em formato PDF que corresponde ao termo de compromisso devidamente preenchido e assinado.</p>
                                <p>O modelo do termo pode ser encontrado neste <a target="_blank" href="{{ route('showTermRequisicao', base64_encode('termos/default.pdf')) }}">link</a>.</p>
                                @if($errors->has('termo'))
                                    <p class="text-danger">{!! $errors->first('termo') !!}</p>
                                @endif
                                <input name="termo" type='file' title="Arquivo PDF do termo de compromisso assinado">
                            </div>
                        </div>

                        <br />

                        <div class="text-center">
                            <button type="button" class="btn bg-ufop" onClick="history.back()"><i class='fa fa-times'></i> Cancelar</button>
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
@endsection
