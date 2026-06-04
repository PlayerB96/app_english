<?php

namespace App\Repositories;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements RepositoryInterface
{
    public function __construct(protected Model $model) {}

    public function all(array $columns = ['*'])
    {
        return $this->model->newQuery()->get($columns);
    }

    public function paginate(int $perPage = 15, array $columns = ['*'])
    {
        return $this->model->newQuery()->paginate($perPage, $columns);
    }

    public function find(int|string $id, array $columns = ['*'])
    {
        return $this->model->newQuery()->findOrFail($id, $columns);
    }

    public function create(array $data)
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(int|string $id, array $data): bool
    {
        $model = $this->find($id);

        return $model->update($data);
    }

    public function delete(int|string $id): bool
    {
        $model = $this->find($id);

        return (bool) $model->delete();
    }
}
