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
                'tokens' => ['Solo los aprendices pueden usar tokens.'],
            ]);
        }

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'tokens' => ['El coste de tokens no es válido.'],
            ]);
        }

        if ($user->tokens < $amount) {
            throw ValidationException::withMessages([
                'tokens' => ["Necesitas {$amount} tokens. Tienes {$user->tokens}."],
            ]);
        }

        $user->decrement('tokens', $amount);

        return $user->fresh()->tokens;
    }
}
