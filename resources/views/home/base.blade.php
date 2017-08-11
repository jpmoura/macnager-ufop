@extends('layout.base')

@section('title')
    Início
@endsection

@section('description')
    Bem-vindo {!! auth()->user()->nome !!}, você está na página inicial.
@endsection

@push('extra-css')
    {!! HTML::style('public/js/plugins/datatables/dataTables.bootstrap.css') !!}
@endpush

@push('extra-scripts')
    {!! HTML::script('public/js/plugins/datatables/jquery.dataTables.min.js') !!}
    {!! HTML::script('public/js/plugins/datatables/dataTables.bootstrap.min.js') !!}
    <script>
        $(function () {
            $(".datatable").DataTable( {
                "language": {
                    "lengthMenu": "Mostrar _MENU_ registros por página",
                    "zeroRecords": "Nada encontrado.",
                    "info": "Mostrando página _PAGE_ de _PAGES_",
                    "infoEmpty": "Nenhum registro disponível",
                    "infoFiltered": "(Filtrado de _MAX_ registros)",
                    "search": "Procurar:",
                    "paginate": {
                        "next": "Próximo",
                        "previous": "Anterior"
                    }
                },
                "autoWidth" : true,
                "aLengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Tudo"]]
            });
        });
    </script>
@endpush