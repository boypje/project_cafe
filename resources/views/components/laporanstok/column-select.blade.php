<div class="form-group row">
    <div class="col-12 col-lg-offset-1">
        <label for="column-select">Pilih Kolom:</label>
        <select id="column-select" name="productIds[]" class="form-control" multiple>
        </select> <input type="checkbox" name="selected_all" id="selected_all" @if (request('selected_all')) checked @endif>Select All
    </div>
</div>

@push('scripts')
<script>
    $('#column-select').select2({
        placeholder: 'Select the columns',
        alowClear: true,
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

    $(document).ready(()=> {
        $('#column-select').on('change', function () {
            const selectedId = $(this).val() || [];
            const selectedOptions = [];

            $(this).find('option:selected').each(function () {
                const id = $(this).val();
                const name = $(this).text();
                if(id == null) return;
                selectedOptions.push({ id, name });
            });
           
            localStorage.setItem('selectedOptions', JSON.stringify(selectedOptions));
        });

        $('#selected_all').on('click', function () {
            if($("#selected_all").is(':checked') ){
                $('#column-select').val(null).trigger('change');
            }
        }); 
    })

    const storedSelectedOptions = localStorage.getItem('selectedOptions');
    if (storedSelectedOptions.length > 0) {
        const parsedSelectedOptions = JSON.parse(storedSelectedOptions);
        parsedSelectedOptions.forEach(({id, name}) => {
            let selected = new Option(name, id, true, true);
            $('#column-select').append(selected).trigger('change');   
        });
    }

    

</script>
@endpush