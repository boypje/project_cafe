<div class="modal fade" id="modal-produk" tabindex="-1" role="dialog" aria-labelledby="modal-produk">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Pilih Produk</h4>
            </div>
            <div class="modal-body">
                <table class="table table-striped table-bordered table-produk">
                    <thead>
                        <th width="5%">No</th>
                        <th>Nama</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th><i class="fa fa-cog"></i></th>
                    </thead>
                    <tbody>
                        @foreach ($produk as $key => $item)
                            <tr>
                                <td width="5%">{{ $key+1 }}</td>
                                <td>{{ $item->nama_produk }}</td>
                                <td>{{ $item->harga_jual }}</td>
                                <td>{{ $item->stok }}</td>
                                <td>
                                    @if($item->stok > 0)
                                    <a href="#" class="btn btn-primary btn-xs btn-flat"
                                        onclick="pilihProduk('{{ $item->id_produk }}', '{{ $item->nama_produk }}')">
                                        <i class="fa fa-check-circle"></i>
                                        Pilih
                                    </a>
                                    @else
                                    <a href="#" class="btn btn-danger btn-xs btn-flat">
                                    <i class="fa fa-times"></i>
                                        Habis
                                    </a>
                                    @endif
                                    
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
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


</style>