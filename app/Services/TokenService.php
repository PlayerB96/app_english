<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class TokenService
{
    public function spend(User $user, int $amount, string $reason): int
    {
        if (! $user->isLearner()) {
            throw ValidationException::withMessages([
                'tokens' => ['Solo los aprendices pueden usar poder.'],
            ]);
        }

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'tokens' => ['El coste de poder no es válido.'],
            ]);
        }

        if ($user->tokens < $amount) {
            throw ValidationException::withMessages([
                'tokens' => ["Necesitas {$amount} de poder. Tienes {$user->tokens}."],
            ]);
        }

        $user->decrement('tokens', $amount);

        return $user->fresh()->tokens;
    }

    public function earn(User $user, int $amount, string $reason): int
    {
        if (! $user->isLearner()) {
            throw ValidationException::withMessages([
                'tokens' => ['Solo los aprendices pueden recibir poder.'],
            ]);
        }

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'tokens' => ['La recompensa de poder no es válida.'],
            ]);
        }

        $user->increment('tokens', $amount);

        return $user->fresh()->tokens;
    }
}
