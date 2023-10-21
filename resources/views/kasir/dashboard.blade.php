@extends('layouts.master')

@section('title')
    Dashboard
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Dashboard</li>
@endsection

@section('content')
<!-- Small boxes (Stat box) -->
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-body text-center"> <!-- Tambah class text-center di sini -->
                <h1>Selamat Datang</h1>
                <h2>Anda login sebagai {{ auth()->user()->name }}</h2>
                <br><br>
                <a href="{{ route('transaksi.baru') }}" class="btn btn-success btn-lg">Transaksi Baru</a>
                <br><br>
                <a href="{{ route('kasir.nota_status') }}" class="btn btn-primary btn-lg" id="status">&ensp;Cetak Status&ensp;</a>
                <br><br><br>
            </div>
        </div>
    </div>
</div>
<!-- /.row (main row) -->
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#status').click(function(e) {
            e.preventDefault();
            var url = $(this).attr('href');

            var iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            iframe.src = url;

            document.body.appendChild(iframe);

            iframe.onload = function() {
                iframe.contentWindow.print();
            };
        });
    });
</script>
@endpush
