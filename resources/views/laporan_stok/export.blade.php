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
            <div class="box-body table-responsive">
                @include('laporan_stok.table', ['products' => $products, 'tanggalAwal' => $tanggalAwal, 'tanggalAkhir' => $tanggalAkhir])
            </div>
        </div>
    </div>
</div>
@endsection
