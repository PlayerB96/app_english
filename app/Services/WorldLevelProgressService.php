<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserWorldLevelProgress;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class WorldLevelProgressService
{
    public function __construct(
        private readonly WorldQuestionCatalogService $catalog,
        private readonly WorldProgressService $worldProgress,
    ) {}

    public function questionsPerLevel(): int
    {
        return $this->catalog->questionsPerLevel();
    }

    /**
     * @return list<int>
     */
    public function ensureSessionQuestions(User $user, int $levelId): array
    {
        $this->assertPlayable($user, $levelId);

        $required = $this->questionsPerLevel();
        $pool = $this->catalog->questionIdsForLevel($levelId);

        if (count($pool) < $required) {
            throw new InvalidArgumentException('El banco de preguntas del desafío es insuficiente.');
        }

        $row = $this->rowFor($user, $levelId);

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
     * @return array{completed: bool, correct: int, total: int}
     */
    public function recordQuestionPass(User $user, int $levelId, int $questionId): array
    {
        if (! $this->catalog->questionBelongsToLevel($levelId, $questionId)) {
            throw new InvalidArgumentException('La pregunta no pertenece a este desafío.');
        }

        $this->assertPlayable($user, $levelId);

        $required = $this->questionsPerLevel();
        $row = $this->rowFor($user, $levelId);

        /** @var list<int> $session */
        $session = array_values(array_map('intval', $row->session_question_ids ?? []));

        if ($session !== [] && ! in_array($questionId, $session, true)) {
            throw new InvalidArgumentException('La pregunta no pertenece a la sesión activa.');
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
            $row->save();

            $this->worldProgress->syncCompletedLevel($user, $levelId);
        } else {
            $row->save();
        }

        return [
            'completed' => $row->completed_at !== null,
            'correct' => min(count($answered), $required),
            'total' => $required,
        ];
    }

    public function recordFail(User $user, int $levelId, int $hours = 24): Carbon
    {
        if (! $this->worldProgress->isUnlocked($user, $levelId)) {
            throw new InvalidArgumentException('El desafío no está desbloqueado.');
        }

        $lockedUntil = now()->addHours($hours);
        $row = $this->rowFor($user, $levelId);
        $row->locked_until = $lockedUntil;
        $row->correct_question_ids = [];
        $row->session_question_ids = null;
        $row->save();

        return $lockedUntil;
    }

    public function isLockedOut(User $user, int $levelId): bool
    {
        $row = UserWorldLevelProgress::query()
            ->where('user_id', $user->id)
            ->where('level_id', $levelId)
            ->first();

        if ($row === null || $row->locked_until === null) {
            return false;
        }

        return $row->locked_until->isFuture();
    }

    /**
     * @return array{
     *     lockouts: array<string, string>,
     *     question_progress: array<string, array{correct: int, total: int}>,
     *     answered_questions: array<string, list<int>>,
     *     session_questions: array<string, list<int>>
     * }
     */
    public function quizSnapshot(User $user): array
    {
        if (! $user->world_unlocked_at) {
            return [
                'lockouts' => [],
                'question_progress' => [],
                'answered_questions' => [],
                'session_questions' => [],
            ];
        }

        $rows = UserWorldLevelProgress::query()
            ->where('user_id', $user->id)
            ->get();

        $required = $this->questionsPerLevel();
        $lockouts = [];
        $questionProgress = [];
        $answeredQuestions = [];
        $sessionQuestions = [];

        foreach ($rows as $row) {
            $key = (string) $row->level_id;

            if ($row->locked_until !== null && $row->locked_until->isFuture()) {
                $lockouts[$key] = $row->locked_until->toIso8601String();
            }

            if ($row->completed_at !== null) {
                continue;
            }

            /** @var list<int> $answered */
            $answered = array_values(array_map('intval', $row->correct_question_ids ?? []));

            if ($answered !== []) {
                $questionProgress[$key] = [
                    'correct' => count($answered),
                    'total' => $required,
                ];
                $answeredQuestions[$key] = $answered;
            }

            /** @var list<int>|null $session */
            $session = $row->session_question_ids;

            if ($session !== null && $session !== []) {
                $sessionQuestions[$key] = array_values(array_map('intval', $session));
            }
        }

        return [
            'lockouts' => $lockouts,
            'question_progress' => $questionProgress,
            'answered_questions' => $answeredQuestions,
            'session_questions' => $sessionQuestions,
        ];
    }

    private function assertPlayable(User $user, int $levelId): void
    {
        if (! $user->world_unlocked_at) {
            throw new InvalidArgumentException('Debes desbloquear el Mundo primero.');
        }

        if (! $this->worldProgress->isUnlocked($user, $levelId)) {
            throw new InvalidArgumentException('El desafío no está desbloqueado.');
        }

        if ($this->isLockedOut($user, $levelId)) {
            throw new InvalidArgumentException('El desafío está bloqueado.');
        }

        if ($this->worldProgress->isCompleted($user, $levelId)) {
            throw new InvalidArgumentException('El desafío ya está completado.');
        }
    }

    private function rowFor(User $user, int $levelId): UserWorldLevelProgress
    {
        return UserWorldLevelProgress::query()->firstOrNew([
            'user_id' => $user->id,
            'level_id' => $levelId,
        ]);
    }
}
