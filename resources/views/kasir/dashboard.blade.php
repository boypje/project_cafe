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
                <button class="btn btn-primary btn-lg" onclick="#">&ensp;Cetak Status&ensp;</button>
                <br><br><br>
            </div>
        </div>
    </div>
</div>
<!-- /.row (main row) -->
@endsection
