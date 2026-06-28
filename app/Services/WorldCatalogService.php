<?php

namespace App\Services;

use InvalidArgumentException;

class WorldCatalogService
{
    /**
     * @return list<array<string, mixed>>
     */
    public function worlds(): array
    {
        /** @var list<array<string, mixed>> */
        return config('world.worlds', []);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function levels(): array
    {
        /** @var list<array<string, mixed>> */
        return config('world.levels', []);
    }

    public function totalLevels(): int
    {
        return count($this->levels());
    }

    public function levelExists(int $levelId): bool
    {
        foreach ($this->levels() as $level) {
            if ((int) ($level['id'] ?? 0) === $levelId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function level(int $levelId): ?array
    {
        foreach ($this->levels() as $level) {
            if ((int) ($level['id'] ?? 0) === $levelId) {
                return $level;
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function worldForTier(string $tier): ?array
    {
        foreach ($this->worlds() as $world) {
            if (($world['tier'] ?? '') === $tier) {
                return $world;
            }
        }

        return null;
    }

    public function isWorldAvailable(string $tier): bool
    {
        $world = $this->worldForTier($tier);

        return ($world['status'] ?? '') === 'available';
    }

    public function tierForLevel(int $levelId): string
    {
        $level = $this->level($levelId);

        if ($level === null) {
            throw new InvalidArgumentException('Desafío inválido.');
        }

        return (string) $level['tier'];
    }

    public function worldNameForTier(string $tier): string
    {
        $world = $this->worldForTier($tier);

        if ($world === null) {
            return $tier;
        }

        $emoji = trim((string) ($world['emoji'] ?? ''));

        return $emoji !== ''
            ? "{$emoji} {$world['name']}"
            : (string) $world['name'];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function zoneForLevel(int $levelId): ?array
    {
        $level = $this->level($levelId);

        if ($level === null) {
            return null;
        }

        $zoneSlug = (string) ($level['zone'] ?? '');
        $world = $this->worldForTier((string) $level['tier']);

        if ($world === null) {
            return null;
        }

        foreach ($world['zones'] ?? [] as $zone) {
            if (($zone['slug'] ?? '') === $zoneSlug) {
                return $zone;
            }
        }

        if ($zoneSlug === 'final-boss') {
            return [
                'slug' => 'final-boss',
                'emoji' => $world['boss']['emoji'] ?? '🏁',
                'name' => 'Final Boss',
                'level_range' => '18',
            ];
        }

        return null;
    }
}
