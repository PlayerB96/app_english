<?php

namespace App\Repositories\Contracts;

use App\DTOs\Auth\MobileUserValidationRowDto;

interface AuthRepositoryInterface
{
    /**
     * @return list<MobileUserValidationRowDto>
     */
    public function validateCredentials(string $username, string $password): array;
}
