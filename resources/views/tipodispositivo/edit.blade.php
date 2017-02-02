@extends('layout.base')

@section('dispositivo')
    active
@endsection

@section('tipodispositivo')
    active
@endsection

@section('title')
    Editar Tipo de Dispositivo
@endsection

@section('description')
    Altere o campo para editar o tipo de dispositivo.
@endsection

@section('breadcrumb')
    <li><i class="fa fa-laptop"></i> Dispositivos</li>
    <li><i class="fa fa-puzzle-piece"></i> Tipos</li>
    <li><i class="fa fa-edit"></i> Editar</li>
@endsection

@section('content')
    <div class='row'>
        <div class='col-lg-12'>
            <div class="box box-primary-ufop">
                <div class="box-body">
                    <form class="form" action="{{ route('updateTipoDispositivo') }}" accept-charset="UTF-8" method="post">
                        {{ csrf_field() }}

                        <input type="hidden" name="id" value="{{$tipo->id}}">

                        <div class="form-group {{ $errors->has('descricao') ? 'has-error' : '' }}">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-users"></i></span>
                                <input type="text" maxlength="50" name="descricao" class="form-control" @if($errors->has('descricao')) value="{{ old('descricao') }}" @else value="{{ $tipo->descricao }}" @endif placeholder="Nome do tipo do dispositivo" title="Nome do tipo do dispositivo" required data-toggle="tooltip" data-placement="top">
                            </div>
                            @if($errors->has('descricao'))
                                <p class="help-block">{!! $errors->first('descricao') !!}</p>
                            @endif
                        </div>

                        <div class="text-center">
                            <button type="button" class="btn btn-warning" onClick="history.back()">Cancelar <i class='fa fa-times'></i></button>
                            <button type="reset" class="btn btn-info">Limpar <i class='fa fa-eraser'></i></button>
                            <button type="submit" class="btn btn-success">Aplicar <i class='fa fa-check'></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div><!-- /.col -->
    </div><!-- /.row -->
@endsection
