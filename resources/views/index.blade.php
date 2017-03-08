@extends('layout.base')

@section('title')
    Início
@endsection

@section('description')
    Bem-vindo {!! Auth::user()->nome !!}, você está na página inicial.
@endsection

@section('content')
    {!! HTML::script('public/js/plugins/chartjs/Chart.min.js') !!}

    <div class='row'>
        @can('administrate')
            <div class="col-md-12">
                <div class="box box-primary-ufop">
                    <div class="text-center">
                        <h3>Resumo Estatístico da Rede</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-4">
                                <canvas id="devicesPerUserType">
                                    {!! $charts['devicesPerUserType']->render() !!}
                                </canvas>
                            </div>
                            <div class="col-md-4">
                                <canvas id="devicesPerType">
                                    {!! $charts['devicesPerType']->render() !!}
                                </canvas>
                            </div>
                            <div class="col-md-4">
                                <canvas id="requestsPerStatus">
                                    {!! $charts['requestsPerStatus']->render() !!}
                                </canvas>
                            </div>
                        </div>
                        <div class="row">
                            @for($i=0; $i < $subredes->count(); ++$i)
                                <div class="col-md-3">
                                    <canvas id="subrede{{$subredes[$i]->id}}">
                                        {!! $charts['subrede' . $subredes[$i]->id]->render() !!}
                                    </canvas>
                                </div>
                                @if($i % 4 == 0 && $i != 0)</div><div class="row">@endif
                            @endfor
                            @if($i % 4 != 0)</div>@endif
                    </div>
                </div>
            </div>
        @endcan
        @cannot('administrate')
            <div class="row">
                <div class="col-lg-3 col-xs-6">
                    <div class="small-box bg-green">
                        <div class="inner">
                            <h3>{!! $aceitas !!}</h3>
                            <p>Requisições Aceitas</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-thumbs-o-up"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-xs-6">
                    <div class="small-box bg-red">
                        <div class="inner">
                            <h3>{!! $rejeitadas !!}</h3>
                            <p>Requisições Recusadas</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-thumbs-o-down"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-xs-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{!! $vencidas !!}</h3>
                            <p>Requisições Vencidas</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-history"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-xs-6">
                    <div class="small-box bg-yellow">
                        <div class="inner">
                            <h3>{!! $bloqueadas !!}</h3>
                            <p>Requisições Bloqueadas</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-ban"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endcannot
    </div>
@endsection
