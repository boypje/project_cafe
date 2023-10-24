<table class="table table-stiped table-bordered">
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