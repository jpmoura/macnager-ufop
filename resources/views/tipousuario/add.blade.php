@extends('layout.base')

@section('usuarios')
    active
@endsection

@section('tipousuario')
    active
@endsection

@section('addUserType')
    active
@endsection

@section('title')
    Novo Tipo de Usuário
@endsection

@section('description')
    Complete o campo para adicionar um novo tipo de usuário.
@endsection

@section('breadcrumb')
    <li><i class="fa fa-puzzle-piece"></i> Tipos de Usuário</li>
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
                    <form class="form" action="{{ route('addUserType') }}" accept-charset="UTF-8" method="post">
                        {{ csrf_field() }}

                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-puzzle-piece"></i></span>
                            <input type="text" maxlength="50" name="descricao" class="form-control" placeholder="Nome do tipo de usuário" title="Nome do tipo de usuário" required data-toggle="tooltip" data-placement="top">
                        </div>

                        <br />

                        <div class="text-center">
                            <button type="button" class="btn btn-warning" onClick="history.back()">Cancelar <i class='fa fa-times'></i></button>
                            <button type="reset" class="btn btn-info">Limpar <i class='fa fa-eraser'></i></button>
                            <button type="submit" class="btn btn-success">Confirmar <i class='fa fa-check'></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div><!-- /.col -->
    </div><!-- /.row -->
@endsection
