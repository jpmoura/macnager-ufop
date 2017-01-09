<!DOCTYPE html>
<html lang="pt">
<head>
    <title>MACnager - @yield('title')</title>
    <meta charset="UTF-8">
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {!! HTML::style('public/css/bootstrap/bootstrap.min.css') !!}
    {!! HTML::style('public/css/font-awesome/font-awesome.min.css') !!}
    {!! HTML::style('public/css/app.css') !!}

    @stack('extra-css')

    {!! HTML::favicon('public/favicon.ico') !!}
    <link rel="icon" href="{{ asset('public/favicon.ico') }}" type="image/x-icon">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
</head>
<body class="skin-ufop hold-transition sidebar-mini @if(!Auth::check()) guest @endif">

<div class="wrapper">

    @include('layout.header')

    @include('layout.sidebar')

    <div class="content-wrapper">

        <section class="content-header">
            <h1>@yield('title')
                <small>@yield('description')</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="{{ route('home') }}"><i class="fa fa-home"></i> Início</a></li>
                @yield('breadcrumb')
            </ol>
        </section>

        <section class="content">
            @yield('content')
        </section>
    </div>

    @include('layout.footer')
</div>

{!! HTML::script('public/js/app.js') !!}

<!-- Page specific -->
@stack('extra-scripts')
