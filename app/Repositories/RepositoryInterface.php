<?php

namespace App\Repositories;

interface RepositoryInterface
{
    public function all(array $columns = ['*']);
    public function paginate(int $perPage = 15, array $columns = ['*']);
    public function find(int $id, array $columns = ['*']);
    public function findByField(string $field, $value, array $columns = ['*']);
    public function findWhere(array $where, array $columns = ['*']);
    public function create(array $attributes);
    public function update(array $attributes, $id);
    public function delete($id);
}
