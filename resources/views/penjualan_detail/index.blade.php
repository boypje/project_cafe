@extends('layouts.master')

@section('title', 'Transaksi Penjualan')

@push('css')
<style>
    .tampil-bayar {
        font-size: 5.5em;
        text-align: center;
        height: 120px;
        color: #fff;
        background-color: #9DC580;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }

    .all {
        height: 800px;
    }

    .next {
        margin-top: 15px;
        margin-right: 30px;
    }

    .back {
        margin-right: 170px;
    }

    .bagan {
        margin-top: 40px;
    }

    .tampil-terbilang {
        text-align: center;
        font-size: 18px;
        font-family: poppins;
        font-weight: bold;
        padding: 15px;
        background: #f4f1e9;
        border-bottom-left-radius: 8px;
        border-bottom-right-radius: 8px;
    }

    .table-penjualan tbody tr:last-child {
        display: none;
    }

    .form-group input[type="text"],
    .form-group input[type="number"],
    .form-group select {
        border-radius: 8px;
    }

    @media(max-width: 768px) {
        .tampil-bayar {
            font-size: 3em;
            height: 70px;
            padding-top: 5px;
        }
    }
</style>
@endpush

@section('breadcrumb')
    @parent
    <li class="active">Transaksi Penjualan</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="all box">
            <div class="box-body">
                <form class="form-produk" method="post">
                    @csrf
                    <div class="form-group row">
                        <div class="col-lg-5">
                            <input type="hidden" name="id_penjualan" id="id_penjualan" value="{{ $id_penjualan }}">
                            <input type="hidden" name="id_produk" id="id_produk">
                        </div>
                    </div>
                    <button onclick="tampilProduk()" class="btn btn-info btn-flat" type="button"><i class="fa fa-plus"></i> Pilih Menu</button><br></br>
                </form>

                <table class="table table-stiped table-bordered table-penjualan">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama</th>
                            <th>Harga</th>
                            <th width="15%">Jumlah</th>
                            <th>Subtotal</th>
                            <th width="15%"><i class="fa fa-cog"></i></th>
                        </tr>
                    </thead>
                </table>

                <div class="bagan row">
                    <div class="col-lg-8">
                        <div class="tampil-bayar bg-primary"></div>
                        <div class="tampil-terbilang"></div>
                    </div>
                    <div class="col-lg-4">
                        <form action="{{ route('transaksi.simpan') }}" class="form-penjualan" method="post">
                            @csrf
                            <input type="hidden" name="id_penjualan" value="{{ $id_penjualan }}">
                            <input type="hidden" name="total" id="total">
                            <input type="hidden" name="total_item" id="total_item">
                            <input type="hidden" name="bayar" id="bayar">

                            <div class="form-group row">
                                <label for="pengunjung" class="col-lg-3 control-label">Pengunjung</label>
                                <div class="col-lg-8">
                                    <input type="number" id="pengunjung" class="form-control" name="pengunjung" value="{{ $penjualan->pengunjung ?? 1 }}">
                                </div>
                            </div>
                            
                            <div class="form-group row">
                                <label for="totalrp" class="col-lg-3 control-label">Total</label>
                                <div class="col-lg-8">
                                    <input type="text" id="totalrp" class="form-control" readonly>
                                </div>
                            </div>
                            
                            <div class="form-group row">
                                <label for="diskon" class="col-lg-3 control-label">Potongan</label>
                                <div class="col-lg-8">
                                    <input type="number" name="diskon" id="diskon" class="form-control" 
                                        value="{{ $penjualan->diskon ?? 0 }}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="bayar" class="col-lg-3 control-label">Bayar</label>
                                <div class="col-lg-8">
                                    <input type="text" id="bayarrp" class="form-control" readonly>
                                </div>
                            </div>
                                
                            <div class="form-group row">
                                <label for="diterima" class="col-lg-3 control-label">Diterima</label>
                                <div class="col-lg-8">
                                    <input type="number" id="diterima" class="form-control" name="diterima" value="{{ $penjualan->diterima ?? 0 }}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="metode" class="col-lg-3 control-label">Metode</label>
                                <div class="col-lg-8">
                                    <select name="metode" id="metode" class="form-control" required autofocus>
                                        <option value="Tunai">Tunai</option>
                                        <option value="Debit">Debit</option>
                                    </select>
                                    <span class="help-block with-errors"></span>
                                </div>
                            </div>
                            
                            <div class="form-group row">
                                <label for="kembali" class="col-lg-3 control-label">Kembali</label>
                                <div class="col-lg-8">
                                    <input type="text" id="kembali" name="kembali" class="form-control" value="0" readonly>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="next">
                    <button type="submit" class="btn btn-primary btn-sm btn-flat pull-right btn-simpan"><i class="fa fa-floppy-o"></i> Simpan Transaksi</button>
                </div>

                <div class="back">
                    <button class="btn btn-success btn-sm btn-flat pull-right btn-kembali"><i class="fa fa-arrow-left"></i> Kembali</button>
                </div>
            </div>
        </div>
    </div>
</div>

@includeIf('penjualan_detail.produk')

@endsection

@push('scripts')
<script>
    let table, table2;

    $(function () {
        $('body').addClass('sidebar-collapse');

        table = $('.table-penjualan').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: '{{ route('transaksi.data', $id_penjualan) }}',
            },
            columns: [
                {data: 'DT_RowIndex', searchable: false, sortable: false},
                {data: 'nama_produk'},
                {data: 'harga_jual'},
                {data: 'jumlah'},
                {data: 'subtotal'},
                {data: 'aksi', searchable: false, sortable: false},
            ],
            dom: 'Brt',
            bSort: false,
            paginate: false
        })
        .on('draw.dt', function () {
            loadForm($('#diskon').val());
            setTimeout(() => {
                $('#diterima').trigger('input');
            }, 300);
        });
        table2 = $('.table-produk').DataTable();

        $(document).on('input', '.quantity', function () {
            let id = $(this).data('id');
            let jumlah = parseInt($(this).val());

            if (jumlah < 1) {
                $(this).val(1);
                alert('Jumlah tidak boleh kurang dari 1');
                return;
            }
            if (jumlah > 10000) {
                $(this).val(10000);
                alert('Jumlah tidak boleh lebih dari 10000');
                return;
            }

            $.post(`{{ url('/transaksi') }}/${id}`, {
                    '_token': $('[name=csrf-token]').attr('content'),
                    '_method': 'put',
                    'jumlah': jumlah
                })
                .done(response => {
                    $(this).on('mouseout', function () {
                        table.ajax.reload(() => loadForm($('#diskon').val()));
                    });
                });
        });

        $(document).on('input', '#diskon', function () {
            if ($(this).val() == "") {
                $(this).val(0).select();
            }
            loadForm($(this).val());
        });

        $(document).on('input', '#pengunjung', function () {
            if ($(this).val() < 1) {
                $(this).val(1).select();
            }
        });

        $('#diterima').on('input', function () {
            if ($(this).val() == "") {
                $(this).val(0).select();
            }
            loadForm($('#diskon').val(), $(this).val());
        }).focus(function () {
            $(this).select();
        });
        $('.btn-simpan').on('click', function () {
            $('.form-penjualan').submit();
        });
        $('.btn-kembali').on('click', function () {
            // Mengarahkan ke route 'penjualan.index'
            window.location.href = "{{ route('penjualan.index') }}";
        });
    });

    function tampilProduk() {
        $('#modal-produk').modal('show');
    }

    function pilihProduk(id, nama) {
        $('#id_produk').val(id);
        $('#nama_produk').val(nama);
        tambahProduk();
    }

    function tambahProduk() {
        $.post('{{ route('transaksi.store') }}', $('.form-produk').serialize())
            .done(response => {
                $('#nama_produk').focus();
                table.ajax.reload(() => loadForm($('#diskon').val()));
            })
            .fail(errors => {
                alert('Tidak dapat menyimpan data');
                return;
            });
    }

    function deleteData(url) {
        if (confirm('Yakin ingin menghapus data terpilih?')) {
            $.post(url, {
                    '_token': $('[name=csrf-token]').attr('content'),
                    '_method': 'delete'
                })
                .done((response) => {
                    table.ajax.reload(() => loadForm($('#diskon').val()));
                })
                .fail((errors) => {
                    alert('Tidak dapat menghapus data');
                    return;
                });
        }
    }

    function loadForm(diskon = 0, diterima = 0) {
        $('#total').val($('.total').text());
        $('#total_item').val($('.total_item').text());

        $.get(`{{ url('/transaksi/loadform') }}/${diskon}/${$('.total').text()}/${diterima}`)
            .done(response => {
                $('#totalrp').val(response.totalrp);
                $('#bayarrp').val(response.bayarrp);
                $('#bayar').val(response.bayar);
                $('.tampil-bayar').text('Bayar: '+ response.bayarrp);
                $('.tampil-terbilang').text(response.terbilang);

                $('#kembali').val(response.kembalirp);

                if ($('#diterima').val() != 0) {
                    $('.tampil-bayar').text('Kembali: '+ response.kembalirp);
                    $('.tampil-terbilang').text(response.kembali_terbilang);
                }
            })
            .fail(errors => {
                alert('Tidak dapat menampilkan data');
                return;
            });
    }
</script>
<script>
    function kembali() {
        // Ganti URL berikut dengan URL tujuan Anda
        window.location.href = "{{ route('penjualan.index') }}";
    }
</script>
@endpush
