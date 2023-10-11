<div class="modal fade" id="modal-form" tabindex="-1" role="dialog" aria-labelledby="modal-form">
    <div class="modal-dialog modal-lg" role="document">
        <form action="" method="post" class="form-horizontal">
            @csrf
            @method('post')

            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body">
                    <div class="form-group row">
                        <label for="nama_produk" class="col-lg-2 col-lg-offset-1 control-label">Nama</label>
                        <div class="col-lg-6">
                            <input type="text" name="nama_produk" id="nama_produk" class="form-control" required autofocus
                                @if (auth()->user()->level != 1) readonly
                                @else
                                pattern="[A-Za-z\s]+" title="Hanya huruf yang diperbolehkan"
                                oninput="this.value = this.value.replace(/[^A-Za-z\s]+/g, '')"
                                @endif>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    @if (auth()->user()->level == 1)
                    <div class="form-group row">
                        <label for="id_category" class="col-lg-2 col-lg-offset-1 control-label">Kategori</label>
                        <div class="col-lg-6">
                            <select name="id_category" id="" class="form-control" required">
                                <option value="">Pilih Kategori</option>
                                @foreach ($category as $key => $item)
                                <option value="{{ $key }}">{{ $item }}</option>
                                @endforeach
                            </select>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="harga_jual" class="col-lg-2 col-lg-offset-1 control-label">Harga</label>
                        <div class="col-lg-6">
                            <input type="number" name="harga_jual" id="harga_jual" class="form-control" required autofocus>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    @endif

                    <div class="form-group row">
                        <label for="stok" class="col-lg-2 col-lg-offset-1 control-label">Stok</label>
                        <div class="col-lg-6">
                            <input type="number" name="stok" id="stok" class="form-control" required autofocus>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-flat btn-primary"><i class="fa fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-sm btn-flat btn-warning" data-dismiss="modal"><i class="fa fa-arrow-circle-left"></i> Batal</button>
                </div>
            </div>
        </form>
    </div>
</div>
<style>
   /* Menambahkan radius-border 8px pada modal dan mengatur lebar */
.modal-content {
    border-radius: 8px;
    width: 80%; /* Anda dapat mengganti persentase sesuai dengan kebutuhan Anda */
    max-width: 600px; /* Atur lebar maksimum jika diperlukan */
    left: 50%;
    transform: translate(-50%);
}

/* Menjorokkan field-field input ke kiri */
.modal-content .form-group label {
    text-align: left;
}

/* Menjorokkan field-field input ke kiri */
.modal-content .form-group .form-control {
    text-align: left;
}

/* Opsional: Menambahkan bayangan (box-shadow) untuk efek yang lebih baik */
.modal-content {
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
}

.form-group input[type="text"],
.form-group input[type="number"],
.form-group input[type="email"],
.form-group input[type="password"],
.form-group select {
    border-radius: 8px;
}


</style>