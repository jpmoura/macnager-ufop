@extends('layout.base')

@section('title')
    Cadastrar Usuário
@endsection

@section('page_icon')
    <i class='fa fa-user-plus'></i>
@endsection

@section('description')
    Preencha os campos para adicionar um novo usuário ao sistema.
@endsection

@push('extra-scripts')
<script type="text/javascript">
    $(function(){
        $('#cpf').blur(function(){
            $("#details").html("<img width='36px' height='36px' alt='Carregando...' src='{{ asset('public/img/loading.gif') }}'/>"); // ícone mostrando o carregamento da informação
            $.ajax({
                url: '{{url('/searchperson')}}', // url
                type: "post", // método
                data: {'cpf':$('input[name=cpf]').val(), '_token': $('input[name=_token]').val()}, // dados para o método post

                success: function(response){
                    $("#details").html("<h3>Detalhes do Usuário</h3>");

                    // Se a resposta for OK
                    if(response.status == 'success') { // Achou o usuário
                        $("#details").append("<i class='fa fa-user'></i> " + response.name + "<br />");
                        $("#details").append("<i class='fa fa-envelope'></i> " + response.email + "<br />");
                        $("#details").append("<i class='fa fa-users'></i> " + response.group + "<br />");

                        //alterar os inputs escondidos
                        $('input[name=nome]').val(response.name);
                        $('input[name=email]').val(response.email);

                    }
                    else { // Não encontrou ninguém
                        $("#details").append("<p>" + response.msg + "</p><p>É <span class='text-bold'>necessário</span> que o futuro usuário esteja cadastrado no servidor LDAP.</p><br />");
                        $('input[name=nome]').val('');
                        $('input[name=email]').val('');
                    }
                },

                // Se houver erro na requisição (e.g. 404)
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    $("#details").html(XMLHttpRequest.responseText);
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
                    <form class="form" action="{{ route('ldapuser.store') }}" accept-charset="UTF-8" method="post">
                        {{ csrf_field() }}

                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-user"></i></span>
                            <input type="text" id="cpf" class="form-control" placeholder="CPF do usuário" value="{{ old('cpf') }}" name="cpf" required minlength="11" maxlength="11" data-toggle="tooltip" data-placement="right" title="CPF" >
                        </div>

                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-sitemap"></i></span>
                            <select class="form-control" name="nivel" required title="Nível de privilégio">
                                <option value="">Selecione o nível de privilégio</option>
                                <option value="1" @if(old('nivel') == 1) selected @endif>Administrador</option>
                                <option value="2" @if(old('nivel') == 2) selected @endif>Usuário Comum</option>
                            </select>
                        </div>

                        <input type="hidden" value="" name="nome" required />
                        <input type="hidden" value="" name="email" required />

                        <div id='details' class="row text-center">
                        </div>

                        @if($errors)
                            <div class="row text-center text-danger">
                                @foreach($errors->all() as $erro)
                                    <p>{{ $erro }}</p>
                                @endforeach
                            </div>
                        @endif

                        <br />

                        <div class="text-center">
                            <button type="button" class="btn btn-danger" onClick="history.go(-1)"><i class='fa fa-times'></i> Cancelar</button>
                            <button type="reset" class="btn btn-warning"><i class='fa fa-eraser'></i> Limpar</button>
                            <button type="submit" class="btn btn-success"><i class='fa fa-check'></i> Confirmar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div><!-- /.col -->
    </div><!-- /.row -->
@endsection