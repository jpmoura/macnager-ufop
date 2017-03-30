@extends('layout.base')

@section('title')
    Cadastrar Usuário
@endsection

@section('breadcrumb')
    <li><i class="fa fa-users"></i> Usuários</li>
    <li><i class="fa fa-user-plus"></i> Adicionar</li>
@endsection

@section('description')
    Preencha os campos para adicionar um novo usuário ao sistema.
@endsection

@section('content')
    <div class='row'>
        <div class='col-lg-12'>
            <div class="box box-primary-ufop">
                <div class="box-body">
                    <form class="form" action="{{ route('ldapuser.update', $usuario->id) }}" accept-charset="UTF-8" method="post">
                        {{ csrf_field() }}
                        {{ method_field('PATCH') }}

                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                <input type="text" class="form-control" readonly value="{{ $usuario->nome }}" data-toggle="tooltip" data-placement="top" title="Nome">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                <input type="text" class="form-control" readonly value="{{ $usuario->cpf }}" data-toggle="tooltip" data-placement="top" title="CPF">
                            </div>
                        </div>

                        <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                                <input type="email" name="email" class="form-control" value="{{ $errors->has('email') ? old('email') : $usuario->email }}" data-toggle="tooltip" data-placement="top" title="E-mail" required>
                            </div>
                            @if($errors->has('email'))
                                <span>{!! $errors->first('email') !!}</span>
                            @endif
                        </div>

                        <div class="form-group {{ $errors->has('nivel') ? 'has-error' : '' }}">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-sitemap"></i></span>
                                <select class="form-control" name="nivel" required data-toggle="tooltip" data-placement="top" title="Nível de privilégio">
                                    <option value="">Selecione o nível de privilégio</option>
                                    <option value="1" @if(($errors->has('nivel') && old('nivel') == 1) || (!$errors->has('nivel') && $usuario->nivel == 1)) selected @endif>Administrador</option>
                                    <option value="2" @if(($errors->has('nivel') && old('nivel') == 2) || (!$errors->has('nivel') && $usuario->nivel == 2)) selected @endif>Usuário Comum</option>
                                </select>
                            </div>
                            @if($errors->has('nivel'))
                                <span>{!! $errors->first('nivel') !!}</span>
                            @endif
                        </div>

                        <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-balance-scale"></i></span>
                                <select class="form-control" name="status" required data-toggle="tooltip" data-placement="top" title="Status">
                                    <option value="">Selecione o status do usuário</option>
                                    <option value="0" @if(($errors->has('status') && old('status') == 0) || (!$errors->has('status') && $usuario->status == 0)) selected @endif>Inativo</option>
                                    <option value="1" @if(($errors->has('status') && old('status') == 1) || (!$errors->has('status') && $usuario->status == 1)) selected @endif>Ativo</option>
                                </select>
                            </div>
                            @if($errors->has('status'))
                                <span>{!! $errors->first('status') !!}</span>
                            @endif
                        </div>

                        <br />

                        <div class="text-center">
                            <button type="button" class="btn btn-danger" onClick="history.go(-1)"><i class='fa fa-times'></i> Cancelar</button>
                            <button type="reset" class="btn btn-warning"><i class='fa fa-eraser'></i> Redefinir</button>
                            <button type="submit" class="btn btn-success"><i class='fa fa-check'></i> </button>
                        </div>
                    </form>
                </div>
            </div>
        </div><!-- /.col -->
    </div><!-- /.row -->
@endsection