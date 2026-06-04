<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

/**
 * @internal Plantilla WS-002 — solo tests de patrón. No usar en producción.
 */
class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email): ?User
    {
        $user = $this->model->newQuery()->where('email', $email)->first();

        return $user instanceof User ? $user : null;
    }
}
