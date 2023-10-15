<?php

namespace App\QueryBuilders;

use Illuminate\Database\Eloquent\Builder as BaseBuilder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Spatie\QueryBuilder\QueryBuilder;

abstract class Builder
{
    /**
     * The Query Builder instance for the current resource list generator.
     *
     * @var QueryBuilder
     */
    protected $builder;

    /**
     * Current HTTP Request object.
     *
     * @var Request
     */
    protected $request;

    /**
     * Get a list of allowed columns that can be selected.
     *
     * @return string[]
     */
    abstract protected function getAllowedFields(): array;

    /**
     * Get a list of allowed columns that can be used in any filter operations.
     */
    abstract protected function getAllowedFilters(): array;

    /**
     * Get a list of allowed relationships that can be used in any include operations.
     *
     * @return string[]
     */
    abstract protected function getAllowedIncludes(): array;

    /**
     * Get a list of allowed searchable columns which can be used in any search operations.
     *
     * @return string[]
     */
    abstract protected function getAllowedSearch(): array;

    /**
     * Get a list of allowed columns that can be used in any sort operations.
     *
     * @return string[]
     */
    abstract protected function getAllowedSorts(): array;

    /**
     * Get the query builder instance.
     *
     * @return QueryBuilder|BaseBuilder
     */
    public function getBuilder(): QueryBuilder|BaseBuilder
    {
        $search = strtolower(trim($this->request->input('search') ?? $this->request->input('q')));
        if($search === null) {
            return $this->builder;
        }

        foreach ($this->getAllowedSearch() as $column) {
            $this->builder = $this->builder->orWhere($column, 'LIKE', "%{$search}%");
        }
        return $this->builder;
    }

    /**
     * Get the default sort column that will be used in any sort operation.
     */
    abstract protected function getDefaultSort(): string;

    /**
     * Find the cms admin model based on the given id number.
     *
     *
     * @return mixed
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function find(int $key)
    {
        return $this->query()->findOrFail($key);
    }

    /**
     * Get the paginated results of current API get request.
     */
    public function paginate(): Paginator
    {
        return $this->query()->simplePaginate();
    }

    /**
     * Get default query builder.
     */
    public function query(): QueryBuilder|BaseBuilder
    {
        return $this->getBuilder()
            ->allowedFields($this->getAllowedFields())
            ->allowedFilters($this->getAllowedFilters())
            ->allowedSorts($this->getAllowedSorts())
            ->defaultSort($this->getDefaultSort())
            ->allowedIncludes($this->getAllowedIncludes());
    }

    protected function getAllowedAppends(): array
    {
        return [];
    }
}
