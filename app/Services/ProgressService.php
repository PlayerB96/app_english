<?php

namespace App\Services;

use App\Enums\LevelProgressMode;
use App\Models\Answer;
use App\Models\LearningTrack;
use App\Models\PracticeSession;
use App\Models\ProgressSnapshot;
use App\Models\User;
use App\Models\UserLevelProgress;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ProgressService
{
    /**
     * @return array<string, mixed>
     */
    public function learnerDashboard(User $user): array
    {
        $completedSessions = PracticeSession::query()
            ->where('user_id', $user->id)
            ->where('status', PracticeSession::STATUS_COMPLETED)
            ->get();

        $answers = Answer::query()
            ->where('user_id', $user->id)
            ->get();

        $avgAccuracy = $this->averageAccuracy($answers, $completedSessions);
        $levelEstimated = $this->estimateLevel($user);
        $lastPracticeAt = $this->lastPracticeAt($user, $completedSessions, $answers);

        return [
            'summary' => [
                'total_sessions' => $completedSessions->count(),
                'avg_accuracy' => $avgAccuracy,
                'current_level' => $levelEstimated,
                'streak_days' => $this->calculateStreakDays($user),
                'last_practice_at' => $lastPracticeAt?->toIso8601String(),
                'suggested_level' => $levelEstimated,
                'suggested_track_name' => $this->suggestedTrackName($user),
            ],
            'chart_points' => $this->chartPoints($user),
            'recent_sessions' => $this->recentSessions($user),
        ];
    }

    public function recordQuestionAttempt(
        User $user,
        LevelProgressMode $mode,
        int $questionId,
        int $levelId,
        bool $isCorrect,
        string $responseText,
        string $inputMode,
        bool $closeSession,
    ): void {
        $track = $this->trackForMode($mode);
        $session = $this->activeSession($user, $track);

        Answer::query()->create([
            'question_id' => $questionId,
            'user_id' => $user->id,
            'practice_session_id' => $session->id,
            'response_text' => $responseText,
            'is_correct' => $isCorrect,
            'input_mode' => $inputMode,
            'evaluated_at' => now(),
        ]);

        $session->increment('question_count');

        if ($isCorrect) {
            $session->increment('correct_count');
        }

        if ($closeSession) {
            $this->completeSession($user, $session->fresh(), $mode, $levelId);
        }
    }

    private function trackForMode(LevelProgressMode $mode): LearningTrack
    {
        $slug = $mode === LevelProgressMode::Speaking
            ? config('learning.tracks.speaking.slug')
            : config('learning.tracks.quiz.slug');

        return LearningTrack::query()->where('slug', $slug)->firstOrFail();
    }

    private function activeSession(User $user, LearningTrack $track): PracticeSession
    {
        $existing = PracticeSession::query()
            ->where('user_id', $user->id)
            ->where('learning_track_id', $track->id)
            ->where('status', PracticeSession::STATUS_ACTIVE)
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        return PracticeSession::query()->create([
            'user_id' => $user->id,
            'learning_track_id' => $track->id,
            'status' => PracticeSession::STATUS_ACTIVE,
            'started_at' => now(),
        ]);
    }

    private function completeSession(
        User $user,
        PracticeSession $session,
        LevelProgressMode $mode,
        int $levelId,
    ): void {
        if ($session->status === PracticeSession::STATUS_COMPLETED) {
            return;
        }

        $session->update([
            'status' => PracticeSession::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        if ($session->question_count === 0) {
            return;
        }

        $accuracy = round(($session->correct_count / $session->question_count) * 100, 2);

        ProgressSnapshot::query()->create([
            'user_id' => $user->id,
            'learning_track_id' => $session->learning_track_id,
            'practice_session_id' => $session->id,
            'level_estimated' => $this->estimateLevel($user),
            'accuracy_pct' => $accuracy,
            'total_questions' => $session->question_count,
            'correct_answers' => $session->correct_count,
            'streak_days' => $this->calculateStreakDays($user),
            'snapshot_at' => now(),
            'metadata' => [
                'mode' => $mode->value,
                'level_id' => $levelId,
            ],
        ]);
    }

    /**
     * @param  Collection<int, Answer>  $answers
     * @param  Collection<int, PracticeSession>  $completedSessions
     */
    private function averageAccuracy(Collection $answers, Collection $completedSessions): float
    {
        if ($answers->isNotEmpty()) {
            $correct = $answers->where('is_correct', true)->count();

            return round(($correct / $answers->count()) * 100, 1);
        }

        if ($completedSessions->isEmpty()) {
            return 0.0;
        }

        $totalQuestions = $completedSessions->sum('question_count');

        if ($totalQuestions === 0) {
            return 0.0;
        }

        $correctAnswers = $completedSessions->sum('correct_count');

        return round(($correctAnswers / $totalQuestions) * 100, 1);
    }

    private function estimateLevel(User $user): string
    {
        $completedSublevels = UserLevelProgress::query()
            ->where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->count();

        if ($completedSublevels >= 10) {
            return 'advanced';
        }

        if ($completedSublevels >= 4) {
            return 'intermediate';
        }

        return 'beginner';
    }

    private function suggestedTrackName(User $user): string
    {
        $lastAnswer = Answer::query()
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->with('practiceSession.learningTrack')
            ->first();

        if ($lastAnswer?->practiceSession?->learningTrack !== null) {
            return $lastAnswer->practiceSession->learningTrack->name;
        }

        return LearningTrack::query()
            ->where('slug', config('learning.tracks.speaking.slug'))
            ->value('name') ?? 'Práctica · Speaking';
    }

    /**
     * @param  Collection<int, PracticeSession>  $completedSessions
     * @param  Collection<int, Answer>  $answers
     */
    private function lastPracticeAt(
        User $user,
        Collection $completedSessions,
        Collection $answers,
    ): ?Carbon {
        $fromSession = $completedSessions->max('completed_at');
        $fromAnswer = $answers->max('created_at');

        $candidates = collect([$fromSession, $fromAnswer])
            ->filter()
            ->map(fn ($value) => Carbon::parse($value));

        return $candidates->sortDesc()->first();
    }

    private function calculateStreakDays(User $user): int
    {
        $dates = Answer::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->pluck('created_at')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->unique()
            ->values();

        if ($dates->isEmpty()) {
            return 0;
        }

        $streak = 0;
        $cursor = now()->startOfDay();

        if ($dates->first() !== $cursor->toDateString() && $dates->first() !== $cursor->copy()->subDay()->toDateString()) {
            return 0;
        }

        if ($dates->first() === $cursor->copy()->subDay()->toDateString()) {
            $cursor->subDay();
        }

        foreach ($dates as $date) {
            if ($date !== $cursor->toDateString()) {
                break;
            }

            $streak++;
            $cursor->subDay();
        }

        return $streak;
    }

    /**
     * @return list<array{date: string, accuracy: float|int}>
     */
    private function chartPoints(User $user): array
    {
        $since = now()->subDays(13)->startOfDay();

        $snapshots = ProgressSnapshot::query()
            ->where('user_id', $user->id)
            ->where('snapshot_at', '>=', $since)
            ->orderBy('snapshot_at')
            ->get();

        if ($snapshots->isNotEmpty()) {
            return $snapshots
                ->groupBy(fn (ProgressSnapshot $snapshot) => $snapshot->snapshot_at->toDateString())
                ->map(fn (Collection $group, string $date) => [
                    'date' => $date,
                    'accuracy' => round($group->avg('accuracy_pct'), 1),
                ])
                ->values()
                ->all();
        }

        return Answer::query()
            ->where('user_id', $user->id)
            ->where('created_at', '>=', $since)
            ->orderBy('created_at')
            ->get()
            ->groupBy(fn (Answer $answer) => $answer->created_at->toDateString())
            ->map(fn (Collection $group, string $date) => [
                'date' => $date,
                'accuracy' => $group->count() > 0
                    ? round(($group->where('is_correct', true)->count() / $group->count()) * 100, 1)
                    : 0,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function recentSessions(User $user): array
    {
        return PracticeSession::query()
            ->where('user_id', $user->id)
            ->where('status', PracticeSession::STATUS_COMPLETED)
            ->with('learningTrack')
            ->orderByDesc('completed_at')
            ->limit(10)
            ->get()
            ->map(function (PracticeSession $session): array {
                $accuracy = $session->question_count > 0
                    ? round(($session->correct_count / $session->question_count) * 100, 1)
                    : 0.0;

                return [
                    'id' => $session->id,
                    'track_name' => $session->learningTrack?->name ?? 'Track',
                    'completed_at' => $session->completed_at?->toIso8601String() ?? $session->updated_at->toIso8601String(),
                    'accuracy_pct' => $accuracy,
                    'question_count' => $session->question_count,
                ];
            })
            ->all();
    }
}
