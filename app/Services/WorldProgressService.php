<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserWorldProgress;
use InvalidArgumentException;

class WorldProgressService
{
    public function __construct(
        private readonly WorldCatalogService $catalog,
    ) {}

    public function totalLevels(): int
    {
        return $this->catalog->totalLevels();
    }

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

        for ($levelId = 1; $levelId <= $this->totalLevels(); $levelId += 1) {
            if ($this->catalog->levelExists($levelId) && $this->isUnlocked($user, $levelId, $completed)) {
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

        return $this->catalog->isWorldAvailable($tier);
    }

    private function assertValidLevelId(int $levelId): void
    {
        if ($levelId < 1 || $levelId > $this->totalLevels() || ! $this->catalog->levelExists($levelId)) {
            throw new InvalidArgumentException('Desafío inválido.');
        }
    }
}
