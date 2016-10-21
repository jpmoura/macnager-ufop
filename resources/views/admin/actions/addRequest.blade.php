@extends('admin.admin_base')

@section('requisicoes')
  active
@endsection

@section('addRequest')
  active
@endsection

@section('extracss')
  <link href="{{ asset("plugins/jQueryUI/jquery-ui.min.css")}}" rel="stylesheet" type="text/css" />
@endsection

@section('prescripts')
  <link rel="stylesheet" href="{{asset('plugins/datatables/dataTables.bootstrap.css')}}">
@endsection

@section('title')
  Nova Requisição
@endsection

@section('description')
  Complete os campos para requerer que um dispostivo seja adicionado na rede da UFOP.
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
          <h4 class="text-center text-bold">Todos os campos são obrigatórios</h4>

          <br />

          <form class="form" action="{{url('/addRequest')}}" accept-charset="UTF-8" method="post" enctype="multipart/form-data">
            {{ csrf_field() }}

            <input type="hidden" name="responsavel" value="{{ Session::get('id') }}">
            <input type="hidden" name="responsavelNome" value="{{ Session::get('nome') }}">

            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                <input type="text" value="{{Session::get('nome')}}" disabled class="form-control" title="Pessoa responsável pelo usuário" required data-toggle="tooltip" data-placement="top">
              </div>
              <p class="help-block">Lembre-se que você será o responsável por todo e qualquer desvio de conduta por parte do usuário.</p>
            </div>

            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                <input id="usuario" name="usuario" type="text" minlength="11" maxlength="11" name="usuario" value="{{old('usuario')}}" placeholder="CPF do usuário que irá utilizar o dispositivo" required class="form-control"required>
              </div>
              <p class="help-block">
                Caso o usuário não seja uma pessoa e sim todo um laboratório, por exemplo, você pode clicar
                <a href="#" data-toggle="modal" data-target="#infoModal">aqui</a> para ver a lista de usuários padrões.
              </p>
            </div>

            <div class="row">
              <div id='userDetails' class="col-lg-12"></div>
              <input type="hidden" name="usuarioNome" value="{{old('usuarioNome')}}">
            </div>

            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-users"></i></span>
                <select name="tipousuario" class="form-control" required>
                  <option value="">Selecione um tipo de usuário</option>
                  @foreach ($usuarios as $usuario)
                    <option value="{{$usuario->id}}" @if(old('tipousuario') == $usuario->id) selected @endif>{!! $usuario->descricao !!}</option>
                  @endforeach
                </select>
              </div>
              <p class="help-block">Para usuários do tipo organização, selecione o tipo <a>Recurso Não Dependete de Usuário.</a></p>
            </div>

            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-laptop"></i></span>
                <select name="tipodispositivo" class="form-control" required>
                  <option value="">Selecione um tipo de dispositivo</option>
                  @foreach ($dispositivos as $dispositivo)
                    <option value="{{$dispositivo->id}}" @if (old('tipodispositivo') == $dispositivo->id) selected @endif>{!! $dispositivo->descricao !!}</option>
                  @endforeach
                </select>
              </div>
              <p class="help-block">Tipo do dispositivo que o usuário irá usar pra se conectar na rede da UFOP.</p>
            </div>

            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-hashtag"></i></span>
                <input id="macAddress" name="mac" type='text' class='form-control' value="{{old('mac')}}" placeholder="Endereço MAC da placa de rede" minlength="17" maxlength="17" required>
              </div>
              <p class="help-block">Você pode encontrar o endereço MAC da placa de rede do dispositivo seguindo <a target="_blank" href="{{url('request/' . base64_encode('tutorial-mac.pdf'))}}">este tutorial.</a></p>
            </div>

            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-search"></i></span>
                <input name="descricao" type='text' value="{{old('descricao')}}" class='form-control' placeholder="Descrição do dispositivo" required>
              </div>
              <p class="help-block">Forneça uma breve descrição do dispositivo para que ele possa ser facilmente identificado posteriormente.</p>
            </div>

            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-comment"></i></span>
                <textarea name="justificativa" class="form-control" style="resize: none;" placeholder="Justificativa para adição do dispositivo" maxlength="100" required>{{old('justificativa')}}</textarea>
              </div>
              <p class="help-block">Explique sucintamente a importância deste dispositivo ter acesso a rede da UFOP.</p>
            </div>

            <br />

            <div class="panel panel-danger">
              <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-file-pdf-o"></i> Termo de Compromisso</h3>
              </div>
              <div class="panel-body">
                <p>Selecione o arquivo em formato PDF que corresponde ao termo de compromisso devidamente preenchido e assinado.</p>
                <p>O modelo do termo pode ser encontrado neste <a target="_blank" href="{{url('request/' . base64_encode('termos/default.pdf'))}}">link</a>.</p>
                <input name="termo" type='file' title="Arquivo PDF do termo de compromisso assinado" required>
              </div>
            </div>

            <br />

            <div class="text-center">
              <button type="button" class="btn btn-ufop" onClick="history.back()"><i class='fa fa-times'> Cancelar</i></button>
              <button type="reset" class="btn btn-info"><i class='fa fa-eraser'></i> Limpar</button>
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
                  <td>{{ str_pad($organizacao->cpf, 11, "0", STR_PAD_LEFT) }}</td>
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

@section('extrascripts')
  <script src="{{ asset ('plugins/jQueryMask/jquery.mask.min.js') }}" type="text/javascript"></script>
  <script>
    $(document).ready(function() {
      $('#macAddress').mask('00:00:00:00:00:00', {'translation': {0: {pattern: /[A-Fa-f0-9]/} } } );
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

  <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js')}}"></script>
  <script src="{{ asset('plugins/datatables/dataTables.bootstrap.min.js')}}"></script>
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
@endsection
