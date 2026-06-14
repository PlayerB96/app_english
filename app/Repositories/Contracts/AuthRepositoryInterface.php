<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface AuthRepositoryInterface
{
    /**
     * Valida email + password contra la tabla users.
     * Retorna el usuario autenticado o null si las credenciales no son válidas.
     */
    public function authenticate(string $email, string $password): ?User;
}
