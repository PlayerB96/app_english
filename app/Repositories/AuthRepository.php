<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\AuthRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class AuthRepository implements AuthRepositoryInterface
{
    public function authenticate(string $email, string $password): ?User
    {
        $user = User::query()->where('email', $email)->first();

        if ($user === null || ! Hash::check($password, $user->getAuthPassword())) {
            return null;
        }

        return $user;
    }
}
