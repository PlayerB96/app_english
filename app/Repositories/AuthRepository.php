<?php

namespace App\Repositories;

use App\Enums\UserRole;
use App\Models\User;
use App\Repositories\Contracts\AuthRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class AuthRepository implements AuthRepositoryInterface
{
    public function authenticate(string $email, string $password): ?User
    {
        $user = $this->findByEmail($email);

        if ($user === null || ! $this->verifyPassword($user, $password)) {
            return null;
        }

        return $user;
    }

    public function findByEmail(string $email): ?User
    {
        return User::query()->where('email', $email)->first();
    }

    public function verifyPassword(User $user, string $password): bool
    {
        return Hash::check($password, $user->getAuthPassword());
    }

    public function createUser(string $name, string $email, string $password): User
    {
        return User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => UserRole::Learner,
            'tokens' => config('tokens.initial_balance', 100),
        ]);
    }
}
