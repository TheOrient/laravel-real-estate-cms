<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements RepositoryInterface
{
    protected $model;

    public function __construct()
    {
        $this->makeModel();
    }

    abstract public function model(): string;

    public function makeModel(): Model
    {
        $model = app($this->model());
        return $this->model = $model;
    }

    public function all(array $columns = ['*']): Collection
    {
        return $this->model->all($columns);
    }

    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->model->paginate($perPage, $columns);
    }

    public function find(int $id, array $columns = ['*'])
    {
        return $this->model->find($id, $columns);
    }

    public function findTrashed(int $id, array $columns = ['*'])
    {
        return $this->model->withTrashed()->find($id, $columns);
    }

    public function findByField(string $field, $value, array $columns = ['*'])
    {
        return $this->model->where($field, $value)->get($columns);
    }

    public function findWhere(array $where, array $columns = ['*'])
    {
        $query = $this->model->newQuery();

        foreach ($where as $field => $value) {
            if (is_array($value)) {
                list($field, $operator, $value) = $value;
                $query->where($field, $operator, $value);
            } else {
                $query->where($field, $value);
            }
        }

        return $query->get($columns);
    }

    public function create(array $attributes)
    {
        return $this->model->create($attributes);
    }

    public function update(array $attributes, $id)
    {
        $model = $this->model->findOrFail($id);
        $model->update($attributes);
        return $model;
    }

    public function delete($id)
    {
        $model = $this->model->findOrFail($id);
        return $model->delete();
    }

    public function with($relations): Builder
    {
        return $this->model->with($relations);
    }
}
