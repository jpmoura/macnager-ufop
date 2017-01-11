<!DOCTYPE html>
<html lang="pt">
<head>
    <title>MACnager - Login</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

    {!! HTML::style('public/css/bootstrap/bootstrap.min.css') !!}
    {!! HTML::style('public/css/font-awesome/font-awesome.min.css') !!}
    {!! HTML::style('public/css/app.css') !!}

    {!! HTML::favicon('public/favicon.ico') !!}
    <link rel="icon" href="{{ asset('public/favicon.ico') }}" type="image/x-icon">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
</head>
<body class="hold-transition login-page skin-ufop guest">
<div class="login-box">
    <div class="login-logo">
        <i class="fa fa-desktop"></i> <b>MAC</b>nager
    </div>
    <!-- /.login-logo -->
    <div class="login-box-body ufop-border">
        <p class="login-box-msg">Faça o login para gerenciar seus dispositivos com acesso a rede do campus</p>
        <div class="form">
            <form class="form" action="{{ route('login') }}" method="post">
                {{ csrf_field() }}

                <div class="input-group @if(Session::get('erro') == 1) has-error @endif">
                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                    <input type="text" name="username" class="form-control" maxlength="11" minlength="11" placeholder="CPF do Minha UFOP (Sem números)" required value="{{Input::old('username')}}" @if(Session::get('erro') != 2)  autofocus @endif data-toggle="tooltip" data-placement="right" title="CPF do Minha UFOP" >
                </div>

                <div class="input-group @if(Session::get('erro') == 2) has-error @endif">
                    <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Senha do Minha UFOP" required @if(Session::get('erro') == 2) autofocus @endif data-toggle="tooltip" data-placement="right" title="Senha do Minha UFOP">
                </div>

                <br />

                <div class="text-center">
                    <input type="checkbox" name="remember-me" />
                    <label>Lembre-se de mim</label>
                </div>

                @if(Session::has("mensagem"))
                    <h5 class="text-center text-danger"><b>{!! Session::get("mensagem") !!}</b></h5>
                @endif

                <br />

                <button type="submit" style="background-color: #962038" class="btn btn-primary center-block btn-block"><i class="fa fa-sign-in"></i> Entrar</button>
            </form>
        </div>
        <hr>
        <p class="text-center">Use o <span class="text-bold">mesmo CPF</span> e a <span class="text-bold">mesma senha</span><br /> do <a href="http://www.minha.ufop.br/" target="_blank"><i class="fa fa-home"></i>Minha UFOP</a></p>
    </div>
</div>


<br />

<footer class="text-center">
    <!-- Default to the left -->
    <strong>Copyleft <i class="fa fa-creative-commons"></i> {{ date("Y") }} <a href="https://github.com/jpmoura/macnager-ufop#macnager---sistema-de-gerenciamento-de-rede">NTI ICEA</a></strong>.
</footer>

{!! HTML::script('public/js/app.js') !!}

</body>
</html>
