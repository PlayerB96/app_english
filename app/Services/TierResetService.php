<?php

namespace App\Services;

use App\Enums\LevelProgressMode;
use App\Models\User;
use App\Models\UserTierReset;
use InvalidArgumentException;

class TierResetService
{
    /** @var list<string> */
    private const TIERS = ['basico', 'intermedio', 'avanzado'];

    public function maxResets(): int
    {
        return (int) config('tokens.max_tier_resets', 2);
    }

    public function costForTier(): int
    {
        return (int) config('tokens.tier_reset_cost', 30);
    }

    /** @deprecated Usar costForTier() */
    public function rewardForTier(string $tier): int
    {
        return $this->costForTier();
    }

    public function getCount(User $user, LevelProgressMode $mode, string $tier): int
    {
        $this->assertValidTier($tier);

        $row = UserTierReset::query()
            ->where('user_id', $user->id)
            ->where('mode', $mode->value)
            ->where('tier', $tier)
            ->first();

        return $row?->reset_count ?? 0;
    }

    public function hasResetsRemaining(User $user, LevelProgressMode $mode, string $tier): bool
    {
        return $this->getCount($user, $mode, $tier) < $this->maxResets();
    }

    public function incrementCount(User $user, LevelProgressMode $mode, string $tier): int
    {
        $this->assertValidTier($tier);

        $row = UserTierReset::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'mode' => $mode->value,
                'tier' => $tier,
            ],
            ['reset_count' => 0],
        );

        $row->increment('reset_count');

        return $row->fresh()->reset_count;
    }

    /**
     * @return array<string, array{count: int, max: int, cost: int}>
     */
    public function snapshot(User $user, LevelProgressMode $mode): array
    {
        $max = $this->maxResets();
        $cost = $this->costForTier();
        $counts = UserTierReset::query()
            ->where('user_id', $user->id)
            ->where('mode', $mode->value)
            ->pluck('reset_count', 'tier');

        /** @var array<string, array{count: int, max: int, cost: int}> $result */
        $result = [];

        foreach (self::TIERS as $tier) {
            $result[$tier] = [
                'count' => (int) ($counts[$tier] ?? 0),
                'max' => $max,
                'cost' => $cost,
            ];
        }

        return $result;
    }

    private function assertValidTier(string $tier): void
    {
        if (! in_array($tier, self::TIERS, true)) {
            throw new InvalidArgumentException('Bloque de nivel inválido.');
        }
    }
}
