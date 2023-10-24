<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan</title>

    <link rel="stylesheet" href="{{ asset('/AdminLTE-2/bower_components/bootstrap/dist/css/bootstrap.min.css') }}">
</head>
<body>
    <h3 class="text-center">Laporan Keuangan Restoran Altari</h3>
    <h4 class="text-center">
        Tanggal {{ tanggal_indonesia($awal, false) }}
        s/d
        Tanggal {{ tanggal_indonesia($akhir, false) }}
    </h4>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Kasir</th>
                <th>Total Transaksi</th>
                <th>Transaksi Sukses</th>
                <th>Transaksi Salah</th>
                <th>Total Pengunjung</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($data as $trx)
        <tr>
            @if (is_object($trx))
                <td>{{ $trx->Nomor ?? ''}}</td>
                <td>{{ $trx->Tanggal ?? '' }}</td>
                <td>{{ $trx->kasir ?? '-'}}</td>
                <td>{{ $trx->total_transaksi ?? '-' }}</td>
                <td>{{ $trx->transaksi_sukses ?? '-'}}</td>
                <td>{{ $trx->transaksi_salah ?? '-'}}</td>
                <td>{{ $trx->total_pengunjung ?? '-'}}</td>
            @else
                <td>{{ $trx['Nomor'] }}</td>
                <td>{{ $trx['Tanggal'] ?? '' }}</td>
                <td>{{ $trx['kasir'] ?? '-'}}</td>
                <td>{{ $trx['total_transaksi'] ?? '-' }}</td>
                <td>{{ $trx['transaksi_sukses'] ?? '-'}}</td>
                <td>{{ $trx['transaksi_salah'] ?? '-'}}</td>
                <td>{{ $trx['total_pengunjung'] ?? '-'}}</td>
            @endif

        </tr>

        @endforeach
        </tbody>
    </table>
</body>
</html>
