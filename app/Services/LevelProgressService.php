<?php

namespace App\Services;

use App\Enums\LevelProgressMode;
use App\Models\User;
use App\Models\UserLevelProgress;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class LevelProgressService
{
    public const TOTAL_LEVELS = 15;

    /** @var list<string> */
    private const TIER_ORDER = ['basico', 'intermedio', 'avanzado'];

    public function __construct(
        private readonly ChallengeCatalogService $catalog,
    ) {}

    public function questionsPerLevel(): int
    {
        return (int) config('learning.questions_per_level', 3);
    }

    public function questionsPoolMin(): int
    {
        return (int) config('learning.questions_pool_min', 9);
    }

    /**
     * @return list<int>
     */
    public function ensureSessionQuestions(
        User $user,
        LevelProgressMode $mode,
        int $levelId,
    ): array {
        $this->assertValidLevelId($levelId);

        if (! $this->isUnlocked($user, $mode, $levelId)) {
            throw new InvalidArgumentException('El nivel no está desbloqueado.');
        }

        if ($this->isLockedOut($user, $mode, $levelId)) {
            throw new InvalidArgumentException('El subnivel está bloqueado.');
        }

        if ($this->isCompleted($user, $mode, $levelId)) {
            throw new InvalidArgumentException('El subnivel ya está completado.');
        }

        $required = $this->questionsPerLevel();
        $pool = $this->catalog->questionIdsForLevel($mode, $levelId);

        if (count($pool) < $required) {
            throw new InvalidArgumentException('El banco de preguntas del subnivel es insuficiente.');
        }

        $row = UserLevelProgress::query()->firstOrNew([
            'user_id' => $user->id,
            'mode' => $mode->value,
            'level_id' => $levelId,
        ]);

        /** @var list<int> $session */
        $session = array_values(array_map('intval', $row->session_question_ids ?? []));
        /** @var list<int> $answered */
        $answered = array_values(array_map('intval', $row->correct_question_ids ?? []));

        $sessionIsValid = $answered !== []
            && count($session) === $required
            && empty(array_diff($session, $pool))
            && empty(array_diff($answered, $session));

        if (! $sessionIsValid) {
            $shuffled = $pool;
            shuffle($shuffled);
            $session = array_values(array_slice($shuffled, 0, $required));
            shuffle($session);
            $row->session_question_ids = $session;

            if ($answered !== [] && ! empty(array_diff($answered, $session))) {
                $row->correct_question_ids = [];
            }

            $row->save();
        }

        return $session;
    }

    /**
     * @return array{
     *     unlocked: list<int>,
     *     completed: list<int>,
     *     lockouts: array<string, string>,
     *     question_progress: array<string, array{correct: int, total: int}>,
     *     answered_questions: array<string, list<int>>,
     *     session_questions: array<string, list<int>>
     * }
     */
    public function snapshot(User $user, LevelProgressMode $mode): array
    {
        $rows = UserLevelProgress::query()
            ->where('user_id', $user->id)
            ->where('mode', $mode->value)
            ->get();

        $required = $this->questionsPerLevel();

        $completed = $rows
            ->filter(fn (UserLevelProgress $row) => $row->completed_at !== null)
            ->pluck('level_id')
            ->sort()
            ->values()
            ->all();

        /** @var array<string, string> $lockouts */
        $lockouts = [];

        /** @var array<string, array{correct: int, total: int}> $questionProgress */
        $questionProgress = [];

        /** @var array<string, list<int>> $answeredQuestions */
        $answeredQuestions = [];

        /** @var array<string, list<int>> $sessionQuestions */
        $sessionQuestions = [];

        foreach ($rows as $row) {
            if ($row->locked_until !== null && $row->locked_until->isFuture()) {
                $lockouts[(string) $row->level_id] = $row->locked_until->toIso8601String();
            }

            $ids = $row->correct_question_ids ?? [];
            $correctCount = count($ids);

            if ($correctCount > 0) {
                $answeredQuestions[(string) $row->level_id] = $ids;
            }

            $sessionIds = $row->session_question_ids ?? [];

            if ($sessionIds !== []) {
                $sessionQuestions[(string) $row->level_id] = array_values(array_map('intval', $sessionIds));
            }

            if ($correctCount > 0 && $row->completed_at === null) {
                $questionProgress[(string) $row->level_id] = [
                    'correct' => min($correctCount, $required),
                    'total' => $required,
                ];
            }
        }

        $unlocked = [];

        for ($levelId = 1; $levelId <= self::TOTAL_LEVELS; $levelId++) {
            if ($this->isUnlocked($user, $mode, $levelId, $rows)) {
                $unlocked[] = $levelId;
            }
        }

        return [
            'unlocked' => $unlocked,
            'completed' => $completed,
            'lockouts' => $lockouts,
            'question_progress' => $questionProgress,
            'answered_questions' => $answeredQuestions,
            'session_questions' => $sessionQuestions,
        ];
    }

    /**
     * @return array{completed: bool, correct: int, total: int}
     */
    public function recordQuestionPass(
        User $user,
        LevelProgressMode $mode,
        int $levelId,
        int $questionId,
    ): array {
        $this->assertValidLevelId($levelId);

        if (! $this->catalog->questionBelongsToLevel($mode, $levelId, $questionId)) {
            throw new InvalidArgumentException('La pregunta no pertenece a este nivel.');
        }

        if (! $this->isUnlocked($user, $mode, $levelId)) {
            throw new InvalidArgumentException('El nivel no está desbloqueado.');
        }

        $required = $this->questionsPerLevel();

        $row = UserLevelProgress::query()->firstOrNew([
            'user_id' => $user->id,
            'mode' => $mode->value,
            'level_id' => $levelId,
        ]);

        /** @var list<int> $session */
        $session = array_values(array_map('intval', $row->session_question_ids ?? []));

        if ($session !== [] && ! in_array($questionId, $session, true)) {
            throw new InvalidArgumentException('La pregunta no pertenece a la sesión activa del subnivel.');
        }

        /** @var list<int> $answered */
        $answered = $row->correct_question_ids ?? [];

        if (! in_array($questionId, $answered, true)) {
            $answered[] = $questionId;
        }

        $row->correct_question_ids = array_values($answered);

        if (count($answered) >= $required) {
            $row->completed_at = now();
            $row->locked_until = null;
            $row->session_question_ids = null;
        }

        $row->save();

        return [
            'completed' => $row->completed_at !== null,
            'correct' => min(count($answered), $required),
            'total' => $required,
        ];
    }

    public function recordPass(User $user, LevelProgressMode $mode, int $levelId): void
    {
        $questionIds = $this->catalog->questionIdsForLevel($mode, $levelId);

        foreach ($questionIds as $questionId) {
            $this->recordQuestionPass($user, $mode, $levelId, $questionId);
        }
    }

    public function recordFail(User $user, LevelProgressMode $mode, int $levelId, int $hours = 24): Carbon
    {
        $this->assertValidLevelId($levelId);

        if (! $this->isUnlocked($user, $mode, $levelId)) {
            throw new InvalidArgumentException('El nivel no está desbloqueado.');
        }

        $lockedUntil = now()->addHours($hours);

        $row = UserLevelProgress::query()->firstOrNew([
            'user_id' => $user->id,
            'mode' => $mode->value,
            'level_id' => $levelId,
        ]);

        $row->locked_until = $lockedUntil;
        $row->correct_question_ids = [];
        $row->session_question_ids = null;
        $row->save();

        return $lockedUntil;
    }

    public function skipLockout(User $user, LevelProgressMode $mode, int $levelId): void
    {
        $this->assertValidLevelId($levelId);

        if (! $this->isLockedOut($user, $mode, $levelId)) {
            throw new InvalidArgumentException('El subnivel no está bloqueado.');
        }

        $row = UserLevelProgress::query()
            ->where('user_id', $user->id)
            ->where('mode', $mode->value)
            ->where('level_id', $levelId)
            ->first();

        if ($row === null) {
            throw new InvalidArgumentException('No se encontró el progreso del subnivel.');
        }

        $row->locked_until = null;
        $row->correct_question_ids = [];
        $row->session_question_ids = null;
        $row->save();
    }

    public function reset(User $user, LevelProgressMode $mode): void
    {
        UserLevelProgress::query()
            ->where('user_id', $user->id)
            ->where('mode', $mode->value)
            ->delete();
    }

    public function resetLevel(User $user, LevelProgressMode $mode, int $levelId): void
    {
        $this->assertValidLevelId($levelId);

        UserLevelProgress::query()
            ->where('user_id', $user->id)
            ->where('mode', $mode->value)
            ->where('level_id', $levelId)
            ->delete();
    }

    public function resetTier(User $user, LevelProgressMode $mode, string $tier): void
    {
        if (! $this->canResetTier($user, $mode, $tier)) {
            throw new InvalidArgumentException(
                'No puedes reiniciar este módulo porque ya comenzaste el siguiente.',
            );
        }

        UserLevelProgress::query()
            ->where('user_id', $user->id)
            ->where('mode', $mode->value)
            ->whereIn('level_id', $this->levelIdsForTier($tier))
            ->delete();
    }

    public function canResetTier(User $user, LevelProgressMode $mode, string $tier): bool
    {
        if (! $this->hasTierProgress($user, $mode, $tier)) {
            return false;
        }

        $nextTier = $this->nextTier($tier);

        if ($nextTier === null) {
            return true;
        }

        return ! $this->hasTierProgress($user, $mode, $nextTier);
    }

    public function hasTierProgress(User $user, LevelProgressMode $mode, string $tier): bool
    {
        foreach ($this->levelIdsForTier($tier) as $levelId) {
            if ($this->hasLevelStarted($user, $mode, $levelId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<int>
     */
    public function levelIdsForTier(string $tier): array
    {
        $tierIndex = array_search($tier, self::TIER_ORDER, true);

        if ($tierIndex === false) {
            throw new InvalidArgumentException('Bloque de nivel inválido.');
        }

        $start = ($tierIndex * 5) + 1;

        return range($start, $start + 4);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, UserLevelProgress>|null  $rows
     */
    public function isUnlocked(
        User $user,
        LevelProgressMode $mode,
        int $levelId,
        $rows = null,
    ): bool {
        $this->assertValidLevelId($levelId);

        if ($this->isLockedOut($user, $mode, $levelId, $rows)) {
            return false;
        }

        if ($levelId === 1) {
            return true;
        }

        return $this->isCompleted($user, $mode, $levelId - 1, $rows);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, UserLevelProgress>|null  $rows
     */
    public function isCompleted(
        User $user,
        LevelProgressMode $mode,
        int $levelId,
        $rows = null,
    ): bool {
        $rows ??= $this->rowsFor($user, $mode);

        return $rows->contains(
            fn (UserLevelProgress $row) => $row->level_id === $levelId && $row->completed_at !== null,
        );
    }

    /**
     * @param  \Illuminate\Support\Collection<int, UserLevelProgress>|null  $rows
     */
    public function isLockedOut(
        User $user,
        LevelProgressMode $mode,
        int $levelId,
        $rows = null,
    ): bool {
        $rows ??= $this->rowsFor($user, $mode);

        $row = $rows->firstWhere('level_id', $levelId);

        return $row?->locked_until !== null && $row->locked_until->isFuture();
    }

    /**
     * @return list<int>
     */
    public function correctQuestionIds(User $user, LevelProgressMode $mode, int $levelId): array
    {
        $row = UserLevelProgress::query()
            ->where('user_id', $user->id)
            ->where('mode', $mode->value)
            ->where('level_id', $levelId)
            ->first();

        return $row?->correct_question_ids ?? [];
    }

    private function rowsFor(User $user, LevelProgressMode $mode)
    {
        return UserLevelProgress::query()
            ->where('user_id', $user->id)
            ->where('mode', $mode->value)
            ->get();
    }

    private function assertValidLevelId(int $levelId): void
    {
        if ($levelId < 1 || $levelId > self::TOTAL_LEVELS) {
            throw new InvalidArgumentException('Nivel inválido.');
        }
    }

    private function hasLevelStarted(User $user, LevelProgressMode $mode, int $levelId): bool
    {
        $row = $this->rowsFor($user, $mode)->firstWhere('level_id', $levelId);

        if ($row === null) {
            return false;
        }

        if ($row->completed_at !== null) {
            return true;
        }

        if (! empty($row->correct_question_ids)) {
            return true;
        }

        return $row->locked_until !== null;
    }

    private function nextTier(string $tier): ?string
    {
        $index = array_search($tier, self::TIER_ORDER, true);

        if ($index === false || $index >= count(self::TIER_ORDER) - 1) {
            return null;
        }

        return self::TIER_ORDER[$index + 1];
    }
}
