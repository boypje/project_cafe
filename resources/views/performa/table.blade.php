<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th width="5%">No</th>
            <th>Tanggal</th>
            <th>Kasir</th>
            <th>Total Transaksi</th>
            <th>Transaksi Sukses</th>
            <th>Transaksi Salah</th>
            <th>Total Pengunjung</th>
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
                url: '{{ route('performa.data', [$tanggalAwal, $tanggalAkhir]) }}',
            },
            columns: [
                {data: 'Nomor'},
                {data: 'Tanggal'},
                {data: 'kasir'},
                {data: 'total_transaksi'},
                {data: 'transaksi_sukses'},
                {data: 'transaksi_salah'},
                {data: 'total_pengunjung'}
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
