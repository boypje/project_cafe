<div class="form-group row">
    <label for="column-select" class="col-lg-2 col-lg-offset-1 control-label">Pilih Menu</label>
    <div class="col-lg-6">
        <select id="column-select" name="productIds[]" class="form-control" multiple>
        </select>
        <input type="checkbox" name="selected_all" id="selected_all">Pilih Semua
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(() => {
        // Mengambil nilai parameter 'selected_all' dari URL
        const urlParams = new URLSearchParams(window.location.search);
        const selectedAllParam = urlParams.get('selected_all');

        // Jika parameter 'selected_all' tidak ada atau falsy, maka jangan centang checkbox
        if (!selectedAllParam) {
            $('#selected_all').prop('checked', false);
        }

        $('#selected_all').on('change', function () {
            if (this.checked) {
                $('#column-select').prop('disabled', true); // Menonaktifkan <select> saat checkbox dicentang
                $('#column-select option:selected').each(function () {
                    $(this).prop('selected', false);
                });
                localStorage.removeItem('selectedOptions'); // Hapus selectedOptions dari localStorage
            } else {
                $('#column-select').prop('disabled', false); // Mengaktifkan kembali <select> saat checkbox tidak dicentang
            }
        });

        $('#column-select').select2({
            placeholder: 'Pilih menu untuk kolom laporan',
            allowClear: true,
            ajax: {
                url: '{!! route('produk.get_products') !!}',
                dataType: 'json',
                delay: 250,
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.data.map((item) => {
                            return {
                                id: item.id_produk,
                                text: item.nama_produk,
                                item: item,
                            }
                        }),
                    };
                },
            },
            cache: true,
        });

        $('#column-select').on('change', function () {
            const selectedId = $(this).val() || [];
            const selectedOptions = [];

            $(this).find('option:selected').each(function () {
                const id = $(this).val();
                const name = $(this).text();
                if (id == null) return;
                selectedOptions.push({ id, name });
            });

            localStorage.setItem('selectedOptions', JSON.stringify(selectedOptions));
        });

        const storedSelectedOptions = localStorage.getItem('selectedOptions');
        if (storedSelectedOptions) {
            const parsedSelectedOptions = JSON.parse(storedSelectedOptions);
            parsedSelectedOptions.forEach(({ id, name }) => {
                let selected = new Option(name, id, true, true);
                $('#column-select').append(selected).trigger('change');
            });
        }
    });
</script>
@endpush
<style>
    /* Menambahkan CSS untuk panjang field */
    #column-select {
        width: 270px; /* Sesuaikan dengan panjang yang Anda inginkan */
    }

    /* Mengatur opasitas untuk placeholder */
    #column-select::placeholder {
        opacity: 0.2;
    }
</style>
