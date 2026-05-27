<?php

namespace App\Repositories\Contracts;

interface RepositoryInterface
{
    public function all(array $columns = ['*']);

    public function paginate(int $perPage = 15, array $columns = ['*']);

    public function find(int|string $id, array $columns = ['*']);

    public function create(array $data);

    public function update(int|string $id, array $data): bool;

    public function delete(int|string $id): bool;
}
