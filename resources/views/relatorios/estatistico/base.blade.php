@section('content')
    {!! HTML::script('public/js/plugins/chartjs/Chart.min.js') !!}

    <div class="row">
        <div class="col-lg-12">
            <div class="box box-primary-ufop">
                <div class="box-body">
                    {!! $grafico->render() !!}
                </div>
            </div>
        </div>
    </div>
@endsection