@extends('layout.base')

@section('title')
    Nova Subrede
@endsection

@section('description')
    Complete os campos para adicionar uma nova subrede.
@endsection

@section('breadcrumb')
    <li><i class="fa fa-sitemap"></i> Subredes</li>
    <li><i class="fa fa-plus"></i> Adicionar</li>
@endsection

@push('extra-scripts')
{!! HTML::script('public/js/plugins/jQueryMask/jquery.mask.min.js') !!}
<script>
    $('.ip').mask('0ZZ.0ZZ.0ZZ.0ZZ', {
        translation: {
            'Z': {
                pattern: /[0-9]/, optional: true
            }
        }
    });
</script>
@endpush

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
                    <form class="form" action="{{ route('storeSubrede') }}" accept-charset="UTF-8" method="post">
                        {{ csrf_field() }}

                        <div class="input-group">
                            <span class="input-group-addon">IP</span>
                            <input type="text" minlength="7" maxlength="15" name="endereco" class="form-control ip" placeholder="Endereço inicial da subrede" title="Endereço inicial da subrede" required data-toggle="tooltip" data-placement="top">
                        </div>

                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-eye"></i></span>
                            <select name="cidr" class="form-control" required>
                                <option value="">Selecione uma máscara de rede (CIDR)</option>
                                @for($i = 32; $i > -1; --$i)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="input-group">
                            <span class="input-group-addon"><input id="gateway" type="checkbox" name="gateway"></span>
                            <input class="form-control" readonly value="Ignorar Gateway. Ignorar significa ter um endereço a mais disponível.">
                        </div>

                        <div class="input-group">
                            <span class="input-group-addon"><input type="checkbox" name="broadcast"></span>
                            <input class="form-control" readonly value="Ignorar Gateway. Ignorar significa ter um endereço a mais disponível.">
                        </div>

                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-puzzle-piece"></i></span>
                            <select name="tipo" class="form-control" required>
                                <option value="">Selecione a rede principal a qual essa subrede faz parte</option>
                                @foreach($tipos as $tipo)
                                    <option value="{{ $tipo->id }}">Rede {!! $tipo->descricao !!}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-comment"></i></span>
                            <input type="text" maxlength="75" class="form-control" name="descricao" required placeholder="Descrição da subrede" title="Descrição da subrede." data-toggle="tooltip" data-placement="top">
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
