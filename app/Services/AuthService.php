<?php

namespace App\Services;

use App\Repositories\Contracts\AuthRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(private AuthRepositoryInterface $authRepository) {}

    public function login(string $email, string $password, bool $remember = false): void
    {
        $user = $this->authRepository->authenticate($email, $password);

        if ($user === null) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales no coinciden con nuestros registros.'],
            ]);
        }

        Auth::login($user, $remember);
    }

    public function logout(): void
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }
}
