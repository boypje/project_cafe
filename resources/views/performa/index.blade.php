@extends('layouts.master')

@section('title')
    Laporan Performa Kasir {{ tanggal_indonesia($tanggalAwal, false) }} s/d {{ tanggal_indonesia($tanggalAkhir, false) }}
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('/AdminLTE-2/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
@endpush

@section('breadcrumb')
    @parent
    <li class="active">Performa Kasir</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <button onclick="updatePeriode()" class="btn btn-info btn-xs btn-flat"><i class="fa fa-sliders"></i> Filter</button>
                <a href="{{ route('performa.export_excel', ['tanggal_awal' => $tanggalAwal, 'tanggal_akhir' => $tanggalAkhir, 'user_id' => $user_id]) }}" id="excel" class="btn btn-success btn-xs btn-flat"><i class="fa fa-file-excel-o"></i> Export Excel</a>
                <a href="{{ route('performa.export_pdf', ['tanggal_awal' => $tanggalAwal, 'tanggal_akhir' => $tanggalAkhir, 'user_id' => $user_id]) }}" class="btn btn-danger btn-xs btn-flat" id="cetak-pdf-btn"><i class="fa fa-print"></i> Cetak PDF</a>
            </div>
            <div class="box-body table-responsive">
            @include('performa.table', ['tanggalAwal' => $tanggalAwal, 'tanggalAkhir' => $tanggalAkhir])
            </div>
        </div>
    </div>
</div>

@includeIf('performa.form')
@endsection

@push('scripts')
<script src="{{ asset('/AdminLTE-2/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#cetak-pdf-btn').click(function(e) {
            e.preventDefault();
            var url = $(this).attr('href');

            var iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            iframe.src = url;

            document.body.appendChild(iframe);

            table.ajax.reload();

            iframe.onload = function() {
                iframe.contentWindow.print();
            };
        });
    });
</script>
<script>
    function updatePeriode() {
        $('#modal-form').modal('show');
    }
</script>
<script>
    const excel = document.getElementById('excel');
    excel.addEventListener('click', function(){
        Swal.fire('Success', 'Berhasil Export', 'success');
    })
</script>
@endpush