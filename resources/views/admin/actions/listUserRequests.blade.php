@extends('admin.admin_base')

@section('requisicoes')
  active
@endsection

@section('listUserRequests')
  active
@endsection

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

@section('prescripts')
  <link rel="stylesheet" href="{{asset('plugins/datatables/dataTables.bootstrap.css')}}">
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

      <div class="table">
        <table id="tipos" class="table table-bordered table-striped table-hover text-center">
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
                <td>{{ $requisicao->tipodispositivo }}</td>
                <td>{{ $requisicao->tipousuario }}</td>
                <td>{{ date_format(date_create($requisicao->submissao),"d/m/Y H:i:s") }}</td>
                <td>
                  <span class="text-bold
                  @if ($requisicao->status == 0)
                    text-info">Em Avaliação
                  @elseif($requisicao->status == 1)
                    text-success">Aprovado
                  @elseif($requisicao->status == 2)
                    text-danger">Negado
                  @elseif($requisicao->status == 3)
                    text-danger">Vencido
                  @elseif($requisicao->status == 4)
                    text-warning">Suspenso
                  @endif
                  </span>
                </td>
                <td><a href="{{url('/request/details/'  . $requisicao->id)}}" class="btn btn-xs btn-ufop"><i class="fa fa-search"></i> Detalhes</td>
              </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr>
                <tr>
                  <th>ID</th>
                  <th>Usuário</th>
                  <th>Tipo do Usuário</th>
                  <th>Tipo do Dispositivo</th>
                  <th>Data de Submissão</th>
                  <th>Status</th>
                  <th>Ações</th>
                </tr>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
@endsection

@section('extrascripts')
  <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js')}}"></script>
  <script src="{{ asset('plugins/datatables/dataTables.bootstrap.min.js')}}"></script>
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
@endsection
