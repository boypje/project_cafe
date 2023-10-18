<table class="table table-stiped table-bordered">
    <thead>
        <th>Tanggal</th>
        <th>Kasir</th>
        <th>Total Transaksi</th>
        <th>Total Pengunjung</th>
        @foreach ($products as $product)
            <th>{{ $product->nama_produk }}</th>
        @endforeach
        <th>Total Terjual</th>
    </thead>
</table>

@push('scripts')
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
                @foreach($products as $product)
                    {
                        data: "{{ 'total_produk_' . $product->id_produk . '_terjual'}}",
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

</script>
@endpush