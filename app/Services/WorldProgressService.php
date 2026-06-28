<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserWorldProgress;
use InvalidArgumentException;

class WorldProgressService
{
    public const TOTAL_LEVELS = 15;

    /** @var list<string> */
    private const TIER_ORDER = ['basico', 'intermedio', 'avanzado'];

    public function __construct(
        private readonly WorldCatalogService $catalog,
    ) {}

    /**
     * @return array{unlocked: list<int>, completed: list<int>}
     */
    public function snapshot(User $user): array
    {
        if (! $user->world_unlocked_at) {
            return [
                'unlocked' => [],
                'completed' => [],
            ];
        }

        $completed = UserWorldProgress::query()
            ->where('user_id', $user->id)
            ->orderBy('level_id')
            ->pluck('level_id')
            ->values()
            ->all();

        $unlocked = [];

        for ($levelId = 1; $levelId <= self::TOTAL_LEVELS; $levelId += 1) {
            if ($this->isUnlocked($user, $levelId, $completed)) {
                $unlocked[] = $levelId;
            }
        }

        return [
            'unlocked' => $unlocked,
            'completed' => $completed,
        ];
    }

    public function completeLevel(User $user, int $levelId): void
    {
        $this->assertValidLevelId($levelId);

        if (! $user->world_unlocked_at) {
            throw new InvalidArgumentException('Debes desbloquear el Mundo primero.');
        }

        if (! $this->catalog->levelExists($levelId)) {
            throw new InvalidArgumentException('Desafío inválido.');
        }

        if ($this->isCompleted($user, $levelId)) {
            return;
        }

        if (! $this->isUnlocked($user, $levelId)) {
            throw new InvalidArgumentException('Este desafío aún no está desbloqueado.');
        }

        UserWorldProgress::query()->create([
            'user_id' => $user->id,
            'level_id' => $levelId,
            'completed_at' => now(),
        ]);
    }

    public function isCompleted(User $user, int $levelId, ?array $completed = null): bool
    {
        $completed ??= UserWorldProgress::query()
            ->where('user_id', $user->id)
            ->pluck('level_id')
            ->all();

        return in_array($levelId, $completed, true);
    }

    /**
     * @param  list<int>|null  $completed
     */
    public function isUnlocked(User $user, int $levelId, ?array $completed = null): bool
    {
        $this->assertValidLevelId($levelId);

        if (! $user->world_unlocked_at) {
            return false;
        }

        if (! $this->isWorldTierUnlocked($levelId, $user, $completed)) {
            return false;
        }

        if ($levelId === 1) {
            return true;
        }

        $completed ??= UserWorldProgress::query()
            ->where('user_id', $user->id)
            ->pluck('level_id')
            ->all();

        return in_array($levelId - 1, $completed, true);
    }

    /**
     * @param  list<int>|null  $completed
     */
    private function isWorldTierUnlocked(int $levelId, User $user, ?array $completed = null): bool
    {
        $tier = $this->catalog->tierForLevel($levelId);

        if ($tier === 'basico') {
            return true;
        }

        $completed ??= UserWorldProgress::query()
            ->where('user_id', $user->id)
            ->pluck('level_id')
            ->all();

        $requiredLevel = $tier === 'intermedio' ? 5 : 10;

        return in_array($requiredLevel, $completed, true);
    }

    private function assertValidLevelId(int $levelId): void
    {
        if ($levelId < 1 || $levelId > self::TOTAL_LEVELS) {
            throw new InvalidArgumentException('Desafío inválido.');
        }
    }
}
