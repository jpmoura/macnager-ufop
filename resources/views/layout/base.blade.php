<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>MACnager - @yield('title')</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- Bootstrap 3.3.2 -->
    <link href="{{ asset("public/bootstrap/css/bootstrap.min.css") }}" rel="stylesheet" type="text/css" />
    <!-- Font Awesome Icons -->
    <link href="{{ asset('public/dist/css/font-awesome.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Theme style -->
    <link href="{{ asset("public/dist/css/AdminLTE.min.css") }}" rel="stylesheet" type="text/css" />
    <!-- AdminLTE Skins -->
    <link href="{{ asset("public/dist/css/skins/skin-ufop.css") }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset("public/dist/css/custom.css") }}" rel="stylesheet" type="text/css" />

    @yield('extracss')

    <link rel="shortcut icon" href="{{ asset('public/favicon.ico') }}" type="image/x-icon">
    <link rel="icon" href="{{ asset('public/favicon.ico') }}" type="image/x-icon">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
</head>
<body class="skin-ufop hold-transition sidebar-mini @if(!Auth::check()) guest @endif">

@yield('prescripts')

<div class="wrapper">

    <!-- Header -->
    @include('layout.header')

    <!-- Sidebar -->
    @include('layout.sidebar')

<!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">

        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>@yield('title')
                <small>@yield('description')</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="{{url('/')}}"><i class="fa fa-home"></i> Início</a></li>
                @yield('breadcrumb')
            </ol>
        </section>

        <!-- Main content -->
        <section class="content">
            @yield('content')
        </section><!-- /.content -->
    </div><!-- /.content-wrapper -->

<!-- Footer -->
    @include('layout.footer')
</div><!-- ./wrapper -->

<!-- REQUIRED JS SCRIPTS -->

<!-- jQuery 2.2.4 -->
<script src="{{ asset ('public/plugins/jQuery/jquery-2.2.4.min.js') }}" type="text/javascript"></script>
<!-- Bootstrap 3.3.2 JS -->
<script src="{{ asset ('public/bootstrap/js/bootstrap.min.js') }}" type="text/javascript"></script>
<!-- SlimScroll -->
<script src="{{ asset ('public/plugins/slimScroll/jquery.slimscroll.min.js') }}" type="text/javascript"></script>
<!-- FastClick -->
<script src="{{ asset ('public/plugins/fastclick/fastclick.js') }}" type="text/javascript"></script>
<!-- AdminLTE App -->
<script src="{{ asset ('public/dist/js/app.min.js') }}" type="text/javascript"></script>
<!-- Page specific -->
@yield('extrascripts')
