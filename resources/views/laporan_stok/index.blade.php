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
                <button onclick="updatePeriode()" class="btn btn-info btn-xs btn-flat"><i class="fa fa-plus-circle"></i> Ubah Periode</button>
                <button class="btn btn-success btn-xs btn-flat" onclick="cetakPDF('{{ route('laporan_stok.export_pdf', [$tanggalAwal, $tanggalAkhir]) }}', 'Laporan Stok')">Cetak PDF</button>
            </div>
            <div class="box-header with-border">
                <b>Keterangan</b>
                <br>
                <b>PA :</b> Paket Ayam Endul+Nasi&ensp;<b>PB:</b> Paket Bebek Endul+Nasi&ensp;&ensp;<b>PT:</b> Paket Nasi 3T&ensp;<b>PE:</b> Paket Nasi 4T&ensp;<b>NL:</b> Paket Nasi Lele
                <br>
                <b>BE :</b> Bebek Endul&emsp;<b>AY :</b> Ayam Endul&emsp;<b>NA :</b> Nasi&emsp;<b>TH :</b> Tahu&emsp;<b>TP :</b> Tempe&emsp;<b>TR :</b> Terong&emsp;<b>TL :</b> Telor &emsp;<b>LG :</b> Lele Goreng
                <br>
                <b>MIE :</b> Mie Instan&emsp;&emsp;&nbsp;<b>ME :</b> Mendoan&emsp;&emsp;&nbsp;<b>TT :</b> Teh Tawar&emsp;&ensp;&nbsp;<b>TE :</b> Teh&ensp;&nbsp;&emsp;<b>JE :</b> Jeruk&emsp;<b>KL :</b> K.Lanang&ensp;<b>KS :</b> K.Spesial&ensp;<b>SN :</b> Snack
                <br>
                <b>LE :</b> Le Minerale&emsp;&ensp;<b>AB :</b> Aqua Botol&emsp;&nbsp;<b>AG :</b> Aqua Gelas&emsp;&nbsp;<b>PU :</b> Pucuk Harum&emsp;&nbsp;<b>KO :</b> Teh Kotak&emsp;&nbsp;
                <br>
                <b>DI :</b> Dimsum&emsp;&emsp;&ensp;<b>BOX:</b> Box&emsp;&emsp;&ensp;&emsp;&ensp;<b>KPL:</b> Kepala Ayam
                <br>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-stiped table-bordered">
                    <thead>
                        <th>Tanggal</th>
                        <th>Total Transaksi</th>
                        <th>Total Pengunjung</th>
                        <th width="5%">PA</th>
                        <th width="5%">PB</th>
                        <th width="5%">PT</th>
                        <th width="5%">PE</th>
                        <th width="5%">NL</th>
                        <th width="5%">LG</th>
                        <th width="5%">MIE</th>
                        <th width="5%">BE</th>
                        <th width="5%">AY</th>
                        <th width="5%">NA</th>
                        <th width="5%">TH</th>
                        <th width="5%">TP</th>
                        <th width="5%">TR</th>
                        <th width="5%">TL</th>
                        <th width="5%">ME</th>
                        <th width="5%">TE</th>
                        <th width="5%">TT</th>
                        <th width="5%">JE</th>
                        <th width="5%">KL</th>
                        <th width="5%">KS</th>
                        <th width="5%">SN</th>
                        <th width="5%">LE</th>
                        <th width="5%">AB</th>
                        <th width="5%">AG</th>
                        <th width="5%">PU</th>
                        <th width="5%">KO</th>
                        <th width="5%">DI</th>
                        <th width="5%">BOX</th>
                        <th width="5%">KPL</th>
                        <th>Total Terjual</th>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

@includeIf('laporan_stok.form')
@endsection

@push('scripts')
<script src="{{ asset('/AdminLTE-2/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
<script>
    let table;

    $(function () {
        $('body').addClass('sidebar-collapse');
        table = $('.table').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: '{{ route('laporan_stok.data', [$tanggalAwal, $tanggalAkhir]) }}',
            },
            columns: [
                {data: 'Tanggal'},
                {data: 'kasir'},
                {data: 'total_transaksi'},
                {data: 'total_pengunjung'},
                {data: 'total_produk_23_terjual'},
                {data: 'total_produk_24_terjual'},
                {data: 'total_produk_25_terjual'},
                {data: 'total_produk_26_terjual'},
                {data: 'total_produk_27_terjual'},
                {data: 'total_produk_28_terjual'},
                {data: 'total_produk_29_terjual'},
                {data: 'total_produk_1_terjual'},
                {data: 'total_produk_2_terjual'},
                {data: 'total_produk_3_terjual'},
                {data: 'total_produk_4_terjual'},
                {data: 'total_produk_5_terjual'},
                {data: 'total_produk_6_terjual'},
                {data: 'total_produk_7_terjual'},
                {data: 'total_produk_8_terjual'},
                {data: 'total_produk_9_terjual'},
                {data: 'total_produk_10_terjual'},
                {data: 'total_produk_11_terjual'},
                {data: 'total_produk_12_terjual'},
                {data: 'total_produk_13_terjual'},
                {data: 'total_produk_14_terjual'},
                {data: 'total_produk_15_terjual'},
                {data: 'total_produk_16_terjual'},
                {data: 'total_produk_17_terjual'},
                {data: 'total_produk_18_terjual'},
                {data: 'total_produk_19_terjual'},
                {data: 'total_produk_20_terjual'},
                {data: 'total_produk_21_terjual'},
                {data: 'total_produk_22_terjual'},
                {data: 'total_semua_produk_terjual'},
            ],
            dom: 'Brt',
            bSort: false,
            bPaginate: false,
        });

        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });
    });

    function updatePeriode() {
        $('#modal-form').modal('show');
    }
</script>
<script>
    // tambahkan untuk delete cookie innerHeight terlebih dahulu
    document.cookie = "innerHeight=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    
    function cetakPDF(url, title) {
        popupCenter(url, title, 625, 500);
    }

    function popupCenter(url, title, w, h) {
        const dualScreenLeft = window.screenLeft !==  undefined ? window.screenLeft : window.screenX;
        const dualScreenTop  = window.screenTop  !==  undefined ? window.screenTop  : window.screenY;

        const width  = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
        const height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

        const systemZoom = width / window.screen.availWidth;
        const left       = (width - w) / 2 / systemZoom + dualScreenLeft
        const top        = (height - h) / 2 / systemZoom + dualScreenTop
        const newWindow  = window.open(url, title, 
        `
            scrollbars=yes,
            width  = ${w / systemZoom}, 
            height = ${h / systemZoom}, 
            top    = ${top}, 
            left   = ${left}
        `
        );

        if (window.focus) newWindow.focus();
    }
</script>
@endpush