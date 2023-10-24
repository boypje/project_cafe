<table class="table table-stiped table-bordered">
    <thead>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Kasir</th>
            <th>Total Transaksi</th>
            <th>Transaksi Sukses</th>
            <th>Transaksi Salah</th>\
            <th>Total Pengunjung</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $trx)
        @if (is_array($trx))
        <tr>
            <td>{{ $trx['Nomor'] }}</td>
            <td>{{ $trx['Tanggal'] }}</td>
            <td>{{ $trx['kasir'] }}</td>
            <td>{{ $trx['total_transaksi'] }}</td>
            <td>{{ $trx['transaksi_sukses'] }}</td>
            <td>{{ $trx['transaksi_salah'] }}</td>
            <td>{{ $trx['total_pengunjung'] }}</td>
        </tr>

        @else
        <tr>
            <td>{{ $trx['Nomor'] }}</td>
            <td>{{ $trx['Tanggal'] }}</td>
            <td>{{ $trx['kasir'] }}</td>
            <td>{{ $trx['total_transaksi'] }}</td>
            <td>{{ $trx['transaksi_sukses'] }}</td>
            <td>{{ $trx['transaksi_salah'] }}</td>
            <td>{{ $trx['total_pengunjung'] }}</td>
        </tr>
        @endif


        @endforeach
    </tbody>
</table>