@extends('layout.base')

@section('title')
    Início
@endsection

@section('description')
    Bem-vindo {!! Auth::user()->nome !!}, você está na página inicial.
@endsection

@section('content')
    {!! HTML::script('https://www.gstatic.com/charts/loader.js') !!}

    <div class='col-lg-12'>
        @if(Session::has("tipo"))
            <div class="row">
                <div class="row">
                    <div class="text-center alert alert-dismissible @if(Session::get('tipo') == 'Sucesso') alert-success @elseif(Session::get('tipo') == 'Informação') alert-info @else alert-danger @endif" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <strong>{{Session::get("tipo")}}!</strong> {!! Session::get("mensagem") !!}
                    </div>
                </div>
            </div>
        @endif

        @can('administrate')
            <div class="row">
                <div class="box box-primary-ufop">
                    <div class="box-body">
                        <div class="row">
                            <h3 class="text-center">Taxa de ocupação das faixas</h3>
                        </div>

                        <div class="row">
                            @for($i=0, $f=152; $i < count($faixa) ; $i++, $f++)
                                <div class="col-lg-6 text-center">
                                    <div id="donutchart{{$f}}"></div>
                                    <script type="text/javascript">
                                        google.charts.load("current", {packages:["corechart"]});
                                        google.charts.setOnLoadCallback(drawChart);
                                        function drawChart() {
                                            var data = google.visualization.arrayToDataTable([
                                                ['Faixa', 'Usado (%)'],
                                                ['Em uso', {{$faixa[$f]}}],
                                                ['Livre', {{255 - $faixa[$f]}}]
                                            ]);

                                            var options = {
                                                title: 'Alocação da Faixa {{$f}}',
                                                pieHole: 0.4,
                                            };

                                            var chart = new google.visualization.PieChart(document.getElementById('donutchart{{$f}}'));
                                            chart.draw(data, options);
                                        }
                                    </script>
                                </div>
                            @endfor
                        </div>

                        {{-- <div class="row">
                          <div class="text-center">
                            <a href="{{url('/forceReload')}}" class="btn btn-app bg-olive"><i class="fa fa-refresh"></i> Atualizar</a>
                          </div>
                        </div> --}}
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
