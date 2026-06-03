<?php

namespace App\Repositories\Contracts;

interface AuthRepositoryInterface
{
    /**
     * @return list<object>
     */
    public function validateCredentials(string $username, string $password): array;
}
