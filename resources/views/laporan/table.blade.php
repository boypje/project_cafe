<table class="table table-striped table-bordered">
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
</table>

@push('scripts')
<script src="{{ asset('/AdminLTE-2/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
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
                url: '{{ route('laporan.data', [$tanggalAwal, $tanggalAkhir]) }}',
            },
            columns: [
                {data: 'DT_RowIndex', searchable: false, sortable: false},
                {data: 'tanggal'},
                {data: 'penjualan_tunai'},
                {data: 'penjualan_debit'},
                {data: 'penjualan'},
                {data: 'pengeluaran_tunai'},
                {data: 'pengeluaran_debit'},
                {data: 'pengeluaran'},
                {data: 'pendapatan'}
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
