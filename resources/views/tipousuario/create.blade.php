@extends('layout.base')

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
            <div class="box box-primary-ufop">
                <div class="box-body">
                    <form class="form" action="{{ route('storeTipoUsuario') }}" accept-charset="UTF-8" method="post">
                        {{ csrf_field() }}

                        <div class="form-group {{ $errors->has('descricao') ? 'has-error' : '' }}">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-puzzle-piece"></i></span>
                                <input type="text" maxlength="50" name="descricao" value="{{ old('descricao') }}" class="form-control" placeholder="Nome do tipo de usuário" title="Nome do tipo de usuário" required data-toggle="tooltip" data-placement="top">
                            </div>
                            @if($errors->has('descricao'))
                                <p class="help-block">{{ $errors->first('descricao') }}</p>
                            @endif
                        </div>

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
