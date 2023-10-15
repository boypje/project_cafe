<?php

namespace App\QueryBuilders;

use App\Http\Requests\ProductGetRequest;
use App\Models\Produk;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

final class ProductBuilder extends Builder
{
    /**
     * Current HTTP Request object.
     *
     * @var ProductGetRequest
     */
    protected $request;

    /**
     * BookBuilder constructor.
     *
     * @param ProductGetRequest $request
     */
    public function __construct(ProductGetRequest $request)
    {
        $this->request = $request;
        $this->builder = QueryBuilder::for(Produk::class, $request);
    }

    /**
     * Get a list of allowed columns that can be selected.
     *
     * @return string[]
     */
    protected function getAllowedFields(): array
    {
        return [
            'produk.id_produk',
            'produk.id_category',
            'produk.nama_produk',
            'produk.harga_jual',
            'produk.stok',
        ];
    }

    /**
     * Get a list of allowed columns that can be used in any filter operations.
     *
     * @return array
     */
    protected function getAllowedFilters(): array
    {
        return [
            AllowedFilter::exact('id_produk'),
            'id_category',
            'nama_produk',
            'harga_jual',
            AllowedFilter::exact('stok'),
        ];
    }

    /**
     * Get a list of allowed relationships that can be used in any include operations.
     *
     * @return string[]
     */
    protected function getAllowedIncludes(): array
    {
        return [];
    }

    /**
     * Get a list of allowed searchable columns which can be used in any search operations.
     *
     * @return string[]
     */
    protected function getAllowedSearch(): array
    {
        return [
            'nama_produk',
            'harga_jual',
        ];
    }

    /**
     * Get a list of allowed columns that can be used in any sort operations.
     *
     * @return string[]
     */
    protected function getAllowedSorts(): array
    {
        return [
            'id_produk',
            'nama_produk',
            'harga_jual',
            'stok',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * Get the default sort column that will be used in any sort operation.
     *
     * @return string
     */
    protected function getDefaultSort(): string
    {
        return 'id_produk';
    }
}
