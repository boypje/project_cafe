<script src="{{ asset('js/sweetalert2.min.js') }}"></script>
<link rel="stylesheet" href="{{ asset('css/sweetalert2.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/sweetalert2.css') }}">

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
                        <label for="nama_kategori" class="col-lg-2 col-lg-offset-1 control-label">Kategori</label>
                        <div class="col-lg-6">
                        <input type="text" name="nama_kategori" id="nama_kategori" class="form-control" required autofocus pattern="[A-Za-z\s]+" title="Hanya huruf yang diperbolehkan" oninput="this.value = this.value.replace(/[^A-Za-z\s]+/g, '')">
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-flat btn-primary" id="simpan"><i class="fa fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-sm btn-flat btn-warning" data-dismiss="modal"><i class="fa fa-arrow-circle-left"></i> Batal</button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    .modal-content {
        border-radius: 8px;
        width: 80%; 
        max-width: 600px; 
        left: 50%;
        transform: translate(-50%);
    }
    .modal-content .form-group label {
        text-align: left;
    }
    .modal-content .form-group .form-control {
        text-align: left;
    }

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

