<?php

namespace App\Services;

use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @internal Plantilla WS-002 — solo tests de patrón. No usar en producción.
 */
class UserService
{
    public function __construct(private UserRepositoryInterface $users) {}

    public function list(int $perPage = 15): LengthAwarePaginator
    {
        return $this->users->paginate($perPage);
    }

    public function create(array $data)
    {
        return $this->users->create($data);
    }

    public function all(): Collection
    {
        return $this->users->all();
    }
}
