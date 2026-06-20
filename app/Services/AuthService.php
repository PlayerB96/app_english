<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\AuthRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(private AuthRepositoryInterface $authRepository) {}

    public function login(string $email, string $password, bool $remember = false): void
    {
        $user = $this->authRepository->findByEmail($email);

        if ($user === null) {
            throw ValidationException::withMessages([
                'email' => ['No existe una cuenta registrada con este correo.'],
            ]);
        }

        if (! $this->authRepository->verifyPassword($user, $password)) {
            throw ValidationException::withMessages([
                'password' => ['La contraseña es incorrecta.'],
            ]);
        }

        Auth::login($user, $remember);
    }

    public function register(string $name, string $email, string $password): User
    {
        return $this->authRepository->createUser($name, $email, $password);
    }

    public function logout(): void
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }
}
