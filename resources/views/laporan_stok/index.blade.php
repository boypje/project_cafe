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
                <button onclick="updatePeriode()" class="btn btn-info btn-xs btn-flat"><i class="fa fa-plus-circle"></i> Filter</button>
                <button class="btn btn-success btn-xs btn-flat" 
                    onclick="cetakPDF('{{ route('laporan_stok.export_pdf', [$tanggalAwal, $tanggalAkhir]) }}', 'Laporan Stok')">Cetak PDF</button>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-stiped table-bordered">
                    <thead>
                        <th>Tanggal</th>
                        <th>Kasir</th>
                        <th>Total Transaksi</th>
                        <th>Total Pengunjung</th>
                        @foreach($products as $product)
                            <th width="auto">{{ $product->nama_produk }}</th>
                        @endforeach
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
<script src="{{ asset('/AdminLTE-2/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}">
</script>
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
                url: "{!! route('laporan_stok.data', ['tanggal_awal' => $tanggalAwal, 'tanggal_akhir' => $tanggalAkhir, 'productIds' => $productIds]) !!}",
            },
            columns: [
                {data: 'Tanggal'},
                {data: 'kasir'},
                {data: 'total_transaksi'},
                {data: 'total_pengunjung'},
                @foreach($productIds as $id)
                    {
                        data: "{{ 'total_produk_' . $id . '_terjual'}}",
                    },
                @endforeach
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