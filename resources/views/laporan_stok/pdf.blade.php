<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laporan Penjualan Kasir</title>

    <link rel="stylesheet" href="{{ asset('/AdminLTE-2/bower_components/bootstrap/dist/css/bootstrap.min.css') }}">
</head>
<body>
    <h3 class="text-center">Laporan Penjualan Kasir</h3>
    <h4 class="text-center">
        Tanggal {{ tanggal_indonesia($awal, false) }}
        s/d
        Tanggal {{ tanggal_indonesia($akhir, false) }}
    </h4>

    <div class="box-header with-border">
            </div>

    <table class="table table-striped">
        <thead>
            <tr>
            <th>Tanggal</th>
                        <th>Total Transaksi</th>
                        <th>Total Pengunjung</th>
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
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $row)
                <tr>
                    @foreach ($row as $col)
                        <td>{{ $col }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>