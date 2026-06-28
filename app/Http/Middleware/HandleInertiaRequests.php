<?php

namespace App\Http\Middleware;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user instanceof User ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role->value,
                    ...($user->isLearner() ? [
                        'tokens' => $user->tokens,
                        'world_unlocked' => $user->world_unlocked_at !== null,
                    ] : []),
                ] : null,
            ],
            'flash' => [
                'status' => fn () => $request->session()->get('status'),
            ],
            'game' => [
                'skip_lockout_cost' => (int) config('tokens.skip_lockout_cost', 10),
                'max_tier_resets' => (int) config('tokens.max_tier_resets', 2),
                'tier_reset_cost' => (int) config('tokens.tier_reset_cost', 30),
                'sublevel_complete_reward' => (int) config('tokens.sublevel_complete_reward', 10),
                'world_unlock_cost' => (int) config('tokens.world_unlock_cost', 300),
            ],
        ];
    }
}
