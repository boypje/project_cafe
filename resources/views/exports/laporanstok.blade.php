<table class="table table-stiped table-bordered">
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Kasir</th>
            @foreach($products as $product)
            <th>{{ $product->nama_produk }}</th>
            @endforeach
            <th>Total Terjual</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $trx)
        @if (is_array($trx))
        <tr>
            <td>{{ $trx['Tanggal'] }}</td>
            <td>{{ $trx['kasir'] }}</td>
            @foreach ($products as $product)
            @php
            $columnName = 'total_produk_' . $product->id_produk . '_terjual';
            @endphp
            <td>{{ $trx[$columnName] }}</td>
            @endforeach
            <td>{{ $trx['total_semua_produk_terjual'] }}</td>
        </tr>

        @else
        <tr>
            <td>{{ $trx->Tanggal }}</td>
            <td>{{ $trx->kasir }}</td>
            @foreach ($products as $product)
            @php
            $columnName = 'total_produk_' . $product->id_produk . '_terjual';
            @endphp
            <td>{{ $trx->$columnName }}</td>
            @endforeach
            <td>{{ $trx->total_semua_produk_terjual }}</td>
        </tr>
        @endif


        @endforeach
    </tbody>
</table>