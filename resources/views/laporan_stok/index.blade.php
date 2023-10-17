@extends('layouts.master')

@section('title')
    Laporan Penjualan Kasir {{ tanggal_indonesia($tanggalAwal, false) }} s/d {{ tanggal_indonesia($tanggalAkhir, false) }}
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('/AdminLTE-2/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
<style>
</style>
@endpush

@section('breadcrumb')
    @parent
    <li class="active">Laporan Penjualan Kasir</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <button onclick="updatePeriode()" class="btn btn-info btn-xs btn-flat"><i class="fa fa-sliders"></i> Filter</button>
                @if(isset($tanggalAwal) && isset($tanggalAkhir) && isset($productIds))
                <a href="{{ route('laporan_stok.export_excel', ['tanggal_awal' => $tanggalAwal, 'tanggal_akhir' => $tanggalAkhir, 'productIds' => $productIds]) }}" target="_blank" class="btn btn-success btn-xs btn-flat"><i class="fa fa-file-excel-o"></i> Export Excel</a>
                @endif
            </div>
            <div class="box-body table-responsive">
            @include('laporan_stok.table', ['products' => $products, 'tanggalAwal' => $tanggalAwal, 'tanggalAkhir' => $tanggalAkhir])
            </div>
        </div>
    </div>
</div>

@includeIf('laporan_stok.form')
@endsection

@push('scripts')
<script src="{{ asset('/AdminLTE-2/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}">
</script>

<script>

    function updatePeriode() {
        $('#modal-form').modal('show');
    }
</script>
@endpush