<table class="table table-stiped table-bordered">
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Kasir</th>
            <th>Total Transaksi</th>
            <th>Total Pengunjung</th>
            @foreach($products as $product)
                <th>{{ $product->nama_produk }}</th>
            @endforeach
            <th>Total Terjual</th>
        </tr>
    </thead>
    <tbody>
    @foreach ($data as $trx)
        @if (is_array($trx))
            @continue
        @endif
        <tr>
            <td>{{ $trx->Tanggal }}</td>
            <td>{{ $trx->kasir }}</td>
            <td>{{ $trx->total_transaksi }}</td>
            <td>{{ $trx->total_pengunjung }}</td>
            @foreach ($products as $product)
                @php
                    $columnName = 'total_produk_' . $product->id_produk . '_terjual';
                @endphp
                <td>{{ $trx->$columnName }}</td>
            @endforeach
            <td>{{ $trx->total_semua_produk_terjual }}</td>
        </tr>   
    @endforeach 
    </tbody>
</table>
