<?php

namespace App\Services;

use App\Enums\LevelProgressMode;
use App\Models\Answer;
use App\Models\LearningTrack;
use App\Models\ProgressSnapshot;
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
        private readonly TierResetService $tierResets,
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
     *     session_questions: array<string, list<int>>,
     *     tier_resets: array<string, array{count: int, max: int, cost: int}>
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
            'tier_resets' => $this->tierResets->snapshot($user, $mode),
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
        if (! $this->isTierFullyCompleted($user, $mode, $tier)) {
            throw new InvalidArgumentException(
                'Debes completar los 5 subniveles del módulo antes de reiniciarlo.',
            );
        }

        if (! $this->tierResets->hasResetsRemaining($user, $mode, $tier)) {
            throw new InvalidArgumentException(
                'Ya alcanzaste el máximo de reinicios para este módulo.',
            );
        }

        UserLevelProgress::query()
            ->where('user_id', $user->id)
            ->where('mode', $mode->value)
            ->whereIn('level_id', $this->levelIdsForTier($tier))
            ->delete();
    }

    public function isTierFullyCompleted(User $user, LevelProgressMode $mode, string $tier): bool
    {
        foreach ($this->levelIdsForTier($tier) as $levelId) {
            if (! $this->isCompleted($user, $mode, $levelId)) {
                return false;
            }
        }

        return true;
    }

    public function canResetTier(User $user, LevelProgressMode $mode, string $tier): bool
    {
        if (! $this->isTierFullyCompleted($user, $mode, $tier)) {
            return false;
        }

        return $this->tierResets->hasResetsRemaining($user, $mode, $tier);
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
     * @return array{
     *     level_id: int,
     *     questions: list<array<string, mixed>>,
     *     summary: array{total: int, correct: int, incorrect_attempts: int}
     * }
     */
    public function sublevelReview(User $user, LevelProgressMode $mode, int $levelId): array
    {
        $this->assertValidLevelId($levelId);

        if (! $this->isCompleted($user, $mode, $levelId)) {
            throw new InvalidArgumentException('El subnivel no está completado.');
        }

        $row = UserLevelProgress::query()
            ->where('user_id', $user->id)
            ->where('mode', $mode->value)
            ->where('level_id', $levelId)
            ->first();

        if ($row === null) {
            throw new InvalidArgumentException('No se encontró progreso del subnivel.');
        }

        /** @var list<int> $sessionQuestionIds */
        $sessionQuestionIds = array_values(array_map(
            'intval',
            $row->session_question_ids ?? $row->correct_question_ids ?? [],
        ));

        if ($sessionQuestionIds === []) {
            throw new InvalidArgumentException('No hay preguntas registradas para este subnivel.');
        }

        $trackSlug = $mode === LevelProgressMode::Speaking
            ? config('learning.tracks.speaking.slug')
            : config('learning.tracks.quiz.slug');

        $trackId = LearningTrack::query()->where('slug', $trackSlug)->value('id');

        $snapshot = ProgressSnapshot::query()
            ->where('user_id', $user->id)
            ->where('learning_track_id', $trackId)
            ->where('metadata->mode', $mode->value)
            ->where('metadata->level_id', $levelId)
            ->latest('snapshot_at')
            ->first();

        $answersQuery = Answer::query()
            ->where('user_id', $user->id)
            ->whereIn('question_id', $sessionQuestionIds);

        if ($snapshot !== null) {
            $answersQuery->where('practice_session_id', $snapshot->practice_session_id);
        }

        $answers = $answersQuery->orderBy('evaluated_at')->get();

        if ($answers->isEmpty()) {
            $anchor = Answer::query()
                ->where('user_id', $user->id)
                ->whereIn('question_id', $sessionQuestionIds)
                ->where('is_correct', true)
                ->latest('evaluated_at')
                ->first();

            if ($anchor !== null) {
                $answers = Answer::query()
                    ->where('practice_session_id', $anchor->practice_session_id)
                    ->whereIn('question_id', $sessionQuestionIds)
                    ->orderBy('evaluated_at')
                    ->get();
            }
        }

        $questions = [];
        $incorrectAttempts = 0;
        $finalCorrectCount = 0;

        foreach ($sessionQuestionIds as $questionId) {
            $serialized = $this->catalog->questionById($mode, $questionId);

            if ($serialized === null) {
                continue;
            }

            $questionAttempts = $answers
                ->where('question_id', $questionId)
                ->values()
                ->map(fn (Answer $answer) => [
                    'response_text' => $answer->response_text,
                    'is_correct' => $answer->is_correct,
                    'evaluated_at' => $answer->evaluated_at?->toIso8601String(),
                ])
                ->all();

            $finalCorrect = $questionAttempts !== []
                && (bool) collect($questionAttempts)->last()['is_correct'];

            if ($finalCorrect) {
                $finalCorrectCount++;
            }

            $incorrectAttempts += collect($questionAttempts)
                ->where('is_correct', false)
                ->count();

            $item = [
                'question_id' => $questionId,
                'question_index' => $serialized['question_index'],
                'step_difficulty' => $serialized['step_difficulty'],
                'prompt' => $serialized['prompt'],
                'attempts' => $questionAttempts,
                'final_correct' => $finalCorrect,
            ];

            if ($mode === LevelProgressMode::Speaking) {
                $item['expected_translation'] = $serialized['expected_translation'] ?? '';
            } else {
                $options = $serialized['options'] ?? [];
                $correctIndex = (int) ($serialized['correct_index'] ?? 0);
                $item['expected_answer'] = $options[$correctIndex] ?? '';
            }

            $questions[] = $item;
        }

        usort(
            $questions,
            fn (array $left, array $right) => $left['question_index'] <=> $right['question_index'],
        );

        return [
            'level_id' => $levelId,
            'questions' => $questions,
            'summary' => [
                'total' => count($questions),
                'correct' => $finalCorrectCount,
                'incorrect_attempts' => $incorrectAttempts,
            ],
        ];
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
