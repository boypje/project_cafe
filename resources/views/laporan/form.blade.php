<div class="modal fade" id="modal-form" tabindex="-1" role="dialog" aria-labelledby="modal-form">
    <div class="modal-dialog modal-lg" role="document">
        <form action="{{ route('laporan.index') }}" method="get" data-toggle="validator" class="form-horizontal">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Periode Laporan</h4>
                </div>
                <div class="modal-body">
                <div class="form-group row">
                    <label for="tanggal_awal" class="col-lg-2 col-lg-offset-1 control-label">Tanggal Awal</label>
                    <div class="col-lg-6">
                        <input type="text" name="tanggal_awal" id="tanggal_awal" class="form-control datepicker" required autofocus
                            value="{{ request('tanggal_awal') }}"
                            style="border-radius: 0 !important;"
                            oninput="this.value = this.value.replace(/[^0-9-]/g, '');">
                        <span class="help-block with-errors"></span>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="tanggal_akhir" class="col-lg-2 col-lg-offset-1 control-label">Tanggal Akhir</label>
                    <div class="col-lg-6">
                        <input type="text" name="tanggal_akhir" id="tanggal_akhir" class="form-control datepicker" required
                            value="{{ request('tanggal_akhir') ?? date('Y-m-d') }}"
                            style="border-radius: 0 !important;"
                            oninput="this.value = this.value.replace(/[^0-9-]/g, '');">
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