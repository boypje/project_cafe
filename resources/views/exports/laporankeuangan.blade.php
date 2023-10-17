<table class="table table-stiped table-bordered">
    <thead>
        <tr>
            <th width="5%">No</th>
            <th>Tanggal</th>
            <th>Penjualan Tunai</th>
            <th>Penjualan Debit</th>
            <th>Total Penjualan</th>
            <th>Pengeluaran Tunai</th>
            <th>Pengeluaran Debit</th>
            <th>Total Pengeluaran</th>
            <th>Setoran</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $item)
        @if (is_array($item))
        <tr>
            <td>{{ $item['DT_RowIndex'] }}</td>
            <td>{{ $item['tanggal'] }}</td>
            <td>{{ $item['penjualan_tunai'] }}</td>
            <td>{{ $item['penjualan_debit'] }}</td>
            <td>{{ $item['penjualan'] }}</td>
            <td>{{ $item['pengeluaran_tunai'] }}</td>
            <td>{{ $item['pengeluaran_debit'] }}</td>
            <td>{{ $item['pengeluaran'] }}</td>
            <td>{{ $item['pendapatan'] }}</td>
        </tr>
        @else
        <tr>
            <td>{{ $item['DT_RowIndex'] }}</td>
            <td>{{ $item['tanggal'] }}</td>
            <td>{{ $item['penjualan_tunai'] }}</td>
            <td>{{ $item['penjualan_debit'] }}</td>
            <td>{{ $item['penjualan'] }}</td>
            <td>{{ $item['pengeluaran_tunai'] }}</td>
            <td>{{ $item['pengeluaran_debit'] }}</td>
            <td>{{ $item['pengeluaran'] }}</td>
            <td>{{ $item['pendapatan'] }}</td>
        </tr>
        @endif
        @endforeach
    </tbody>
</table>
