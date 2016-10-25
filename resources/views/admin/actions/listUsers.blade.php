@extends('admin.admin_base')

@section('usuarios')
  active
@endsection

@section('listUsers')
  active
@endsection

@section('title')
  Lista de Usuários @if($id == 1) ativos no últimos mês @else inativos a mais de um mês @endif
@endsection

@section('description')
  Essa é a lista com todos os usuários @if($id == 1) ativos no último mês @else inativos a mais de um mês @endif
@endsection

@section('breadcrumb')
  <li><i class="fa fa-users"></i> Usuários</li>
  <li><i class="fa fa-th-list"></i> Lista de Usuários Frenquentes</li>
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

      <div class="box box-primary-ufop">
        <div class="box-header">
          <ul class="nav nav-tabs">
            <li role="presentation" @if($id == 1) class="active" @endif><a href="{{url('/listUsers/1')}}"><i class="fa fa-check"></i> Ativos</a></li>
            <li role="presentation" @if($id == 2) class="active" @endif><a href="{{url('/listUsers/2')}}"><i class="fa fa-history"></i> Inativos</a></li>
          </ul>
        </div>
        <div class="box-body">
        <div class="table">
          <table id="usuarios" class="table table-bordered table-striped table-hover text-center">
            <thead>
              <tr>
                <th>IP</th>
                <th>Responsável</th>
                <th>Usuário</th>
                <th>Descrição do dispositivo</th>
                @if($id == 1)
                  <th>Enviado</th>
                  <th>Recebido</th>
                  <th>Total</th>
                @endif
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              @foreach($usuarios as $usuario)
                <tr>
                  <td>{{ $usuario->ip }}</td>
                  <td>{!! $usuario->responsavelNome !!}</td>
                  <td>{!! $usuario->usuarioNome !!}</td>
                  <td>{!! $usuario->descricao_dispositivo !!}</td>
                  @if($id == 1)
                    <td>{{ $usuario->sent }}</td>
                    <td>{{ $usuario->received }}</td>
                    <td>{{ $usuario->totalTransferred }}</td>
                  @endif
                  <td><a href="{{ url("editMac" . "/" . $usuario->id) }}" class="btn btn-xs btn-default bg-ufop"><i class="fa fa-edit"></i> Editar</a></td>
                </tr>
                @endforeach
              </tbody>
              <tfoot>
                <tr>
                  <th>IP</th>
                  <th>Responsável</th>
                  <th>Usuário</th>
                  <th>Descrição do dispositivo</th>
                  @if($id == 1)
                    <th>Enviado</th>
                    <th>Recebido</th>
                    <th>Total</th>
                  @endif
                  <th>Ações</th>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>

      </div>
    </div>
@endsection

@section('extrascripts')
  <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js')}}"></script>
  <script src="{{ asset('plugins/datatables/dataTables.bootstrap.min.js')}}"></script>
  <script>
  $(function () {
    $("#usuarios").DataTable( {
      "bSort" : false,
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
