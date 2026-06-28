<?php

namespace App\Services;

use InvalidArgumentException;

class WorldCatalogService
{
    /**
     * @return list<array{tier: string, name: string, description: string}>
     */
    public function worlds(): array
    {
        /** @var list<array{tier: string, name: string, description: string}> */
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
        foreach ($this->worlds() as $world) {
            if ($world['tier'] === $tier) {
                return $world['name'];
            }
        }

        return $tier;
    }
}
