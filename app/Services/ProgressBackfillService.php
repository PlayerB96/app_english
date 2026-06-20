<?php

namespace App\Services;

use App\Enums\LevelProgressMode;
use App\Models\Answer;
use App\Models\LearningTrack;
use App\Models\PracticeSession;
use App\Models\ProgressSnapshot;
use App\Models\Question;
use App\Models\User;
use App\Models\UserLevelProgress;
use Illuminate\Support\Carbon;

/**
 * Migra progreso histórico de user_level_progress a practice_sessions / answers.
 * Necesario para cuentas que avanzaron antes de que existiera la persistencia de métricas.
 */
class ProgressBackfillService
{
    /**
     * @return array{sessions: int, answers: int, snapshots: int, skipped_users: int}
     */
    public function backfill(?int $userId = null, bool $force = false): array
    {
        $stats = [
            'sessions' => 0,
            'answers' => 0,
            'snapshots' => 0,
            'skipped_users' => 0,
        ];

        $query = UserLevelProgress::query()
            ->where(function ($builder) {
                $builder->whereNotNull('completed_at')
                    ->orWhereJsonLength('correct_question_ids', '>', 0);
            })
            ->orderBy('user_id')
            ->orderBy('mode')
            ->orderBy('level_id');

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        /** @var array<int, 'pending'|'skip'|'process'> $userState */
        $userState = [];

        foreach ($query->cursor() as $row) {
            $state = $userState[$row->user_id] ?? 'pending';

            if ($state === 'pending' && ! $force) {
                if (PracticeSession::query()->where('user_id', $row->user_id)->exists()) {
                    $userState[$row->user_id] = 'skip';
                    $stats['skipped_users']++;

                    continue;
                }

                $userState[$row->user_id] = 'process';
            }

            if (($userState[$row->user_id] ?? 'process') === 'skip') {
                continue;
            }

            $questionIds = $row->correct_question_ids ?? [];

            if ($questionIds === [] && $row->completed_at === null) {
                continue;
            }

            $result = $this->backfillRow($row, $questionIds);
            $stats['sessions'] += $result['sessions'];
            $stats['answers'] += $result['answers'];
            $stats['snapshots'] += $result['snapshots'];
        }

        return $stats;
    }

    /**
     * @param  list<int>  $questionIds
     * @return array{sessions: int, answers: int, snapshots: int}
     */
    private function backfillRow(UserLevelProgress $row, array $questionIds): array
    {
        $stats = ['sessions' => 0, 'answers' => 0, 'snapshots' => 0];

        $mode = LevelProgressMode::tryFrom((string) $row->mode);

        if ($mode === null) {
            return $stats;
        }

        $track = $this->trackForMode($mode);
        $user = User::query()->find($row->user_id);

        if ($user === null) {
            return $stats;
        }

        $completedAt = $row->completed_at ?? $row->updated_at ?? now();
        $isCompleted = $row->completed_at !== null;
        $correctCount = count($questionIds);
        $required = (int) config('learning.questions_per_level', 3);
        $questionCount = $isCompleted
            ? max($correctCount, $required)
            : $correctCount;

        if ($questionCount === 0) {
            return $stats;
        }

        $session = PracticeSession::query()->create([
            'user_id' => $user->id,
            'learning_track_id' => $track->id,
            'status' => $isCompleted
                ? PracticeSession::STATUS_COMPLETED
                : PracticeSession::STATUS_ACTIVE,
            'started_at' => Carbon::parse($completedAt)->copy()->subMinutes(max($questionCount, 1)),
            'completed_at' => $isCompleted ? $completedAt : null,
            'question_count' => $questionCount,
            'correct_count' => $correctCount,
        ]);

        $stats['sessions']++;

        foreach ($questionIds as $questionId) {
            if (! Question::query()->whereKey($questionId)->exists()) {
                continue;
            }

            Answer::query()->create([
                'question_id' => (int) $questionId,
                'user_id' => $user->id,
                'practice_session_id' => $session->id,
                'response_text' => '',
                'is_correct' => true,
                'input_mode' => 'backfill',
                'evaluated_at' => $completedAt,
            ]);

            $stats['answers']++;
        }

        if ($isCompleted) {
            $accuracy = round(($correctCount / $questionCount) * 100, 2);

            ProgressSnapshot::query()->create([
                'user_id' => $user->id,
                'learning_track_id' => $track->id,
                'practice_session_id' => $session->id,
                'level_estimated' => $this->estimateLevelAtBackfill($user, Carbon::parse($completedAt)),
                'accuracy_pct' => $accuracy,
                'total_questions' => $questionCount,
                'correct_answers' => $correctCount,
                'streak_days' => 0,
                'snapshot_at' => $completedAt,
                'metadata' => [
                    'backfilled' => true,
                    'mode' => $mode->value,
                    'level_id' => $row->level_id,
                ],
            ]);

            $stats['snapshots']++;
        }

        return $stats;
    }

    private function trackForMode(LevelProgressMode $mode): LearningTrack
    {
        $slug = $mode === LevelProgressMode::Speaking
            ? config('learning.tracks.speaking.slug')
            : config('learning.tracks.quiz.slug');

        return LearningTrack::query()->where('slug', $slug)->firstOrFail();
    }

    private function estimateLevelAtBackfill(User $user, Carbon $at): string
    {
        $completedSublevels = UserLevelProgress::query()
            ->where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->where('completed_at', '<=', $at)
            ->count();

        if ($completedSublevels >= 10) {
            return 'advanced';
        }

        if ($completedSublevels >= 4) {
            return 'intermediate';
        }

        return 'beginner';
    }
}
