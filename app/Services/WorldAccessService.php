<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WorldAccessService
{
    public function unlockCost(): int
    {
        return (int) config('tokens.world_unlock_cost', 300);
    }

    public function hasAccess(User $user): bool
    {
        return $user->world_unlocked_at !== null;
    }

    public function unlock(User $user, TokenService $tokens): User
    {
        if (! $user->isLearner()) {
            throw ValidationException::withMessages([
                'world' => ['Solo los aprendices pueden desbloquear el Mundo.'],
            ]);
        }

        if ($this->hasAccess($user)) {
            throw ValidationException::withMessages([
                'world' => ['El Mundo ya está desbloqueado.'],
            ]);
        }

        return DB::transaction(function () use ($user, $tokens): User {
            $tokens->spend($user, $this->unlockCost(), 'world_unlock');
            $user->forceFill(['world_unlocked_at' => now()])->save();

            return $user->fresh();
        });
    }
}
