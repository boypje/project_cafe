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
                    <label for="name" class="col-lg-3 col-lg-offset-1 control-label">Nama</label>
                        <div class="col-lg-6">
                            <input type="text" name="name" id="name" class="form-control" required autofocus
                                @if (auth()->user()->level != 1) readonly
                                @else
                                pattern="[A-Za-z\s]+" title="Hanya huruf yang diperbolehkan"
                                oninput="this.value = this.value.replace(/[^A-Za-z\s]+/g, '')"
                                @endif>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="email" class="col-lg-3 col-lg-offset-1 control-label">Email</label>
                        <div class="col-lg-6">
                            <input type="email" name="email" id="email" class="form-control" required>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="password" class="col-lg-3 col-lg-offset-1 control-label">Password</label>
                        <div class="col-lg-6">
                            <input type="password" name="password" id="password" class="form-control" 
                            required
                            minlength="6">
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="password_confirmation" class="col-lg-3 col-lg-offset-1 control-label">Konfirmasi Password</label>
                        <div class="col-lg-6">
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" 
                                required
                                data-match="#password">
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