<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Answer;
use App\Models\LearningTrack;
use App\Models\PracticeSession;
use App\Models\User;
use App\Models\UserLevelProgress;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AdminService
{
    /**
     * @return array<string, mixed>
     */
    public function dashboard(): array
    {
        return [
            'kpis' => $this->kpis(),
            'recent_learners' => $this->learners(limit: 5),
            'track_reports' => $this->trackReports(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function learners(?int $limit = null): array
    {
        $rows = User::query()
            ->where('role', UserRole::Learner)
            ->get()
            ->map(fn (User $user) => $this->learnerRow($user))
            ->sortByDesc(fn (array $row) => $row['last_practice_at'] ?? '')
            ->values();

        if ($limit !== null) {
            $rows = $rows->take($limit);
        }

        return $rows->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function tracks(): array
    {
        $sessionCounts = PracticeSession::query()
            ->where('status', PracticeSession::STATUS_COMPLETED)
            ->selectRaw('learning_track_id, COUNT(*) as total')
            ->groupBy('learning_track_id')
            ->pluck('total', 'learning_track_id');

        return LearningTrack::query()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (LearningTrack $track) => [
                'id' => $track->id,
                'slug' => $track->slug,
                'name' => $track->name,
                'description' => $track->description,
                'difficulty' => $track->difficulty,
                'is_active' => $track->is_active,
                'session_count' => (int) ($sessionCounts[$track->id] ?? 0),
            ])
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function trackReports(): array
    {
        return LearningTrack::query()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (LearningTrack $track) => $this->trackReport($track))
            ->all();
    }

    /**
     * @param  array{name?: string, description?: string|null, is_active?: bool, difficulty?: string}  $data
     */
    public function updateTrack(LearningTrack $track, array $data): LearningTrack
    {
        $track->fill(array_intersect_key($data, array_flip([
            'name',
            'description',
            'is_active',
            'difficulty',
        ])));

        $track->save();

        return $track->fresh();
    }

    /**
     * @return array<string, int|float>
     */
    private function kpis(): array
    {
        $completedSessions = PracticeSession::query()
            ->where('status', PracticeSession::STATUS_COMPLETED)
            ->get();

        $answers = Answer::query()->get();

        return [
            'total_learners' => User::query()->where('role', UserRole::Learner)->count(),
            'completed_sessions' => $completedSessions->count(),
            'active_sessions' => PracticeSession::query()
                ->where('status', PracticeSession::STATUS_ACTIVE)
                ->count(),
            'active_tracks' => LearningTrack::query()->where('is_active', true)->count(),
            'avg_accuracy' => $this->globalAverageAccuracy($answers, $completedSessions),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function learnerRow(User $user): array
    {
        $answers = Answer::query()->where('user_id', $user->id)->get();
        $completedSessions = PracticeSession::query()
            ->where('user_id', $user->id)
            ->where('status', PracticeSession::STATUS_COMPLETED)
            ->get();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'sessions_completed' => $completedSessions->count(),
            'last_practice_at' => $this->lastPracticeAt($user, $completedSessions, $answers)?->toIso8601String(),
            'level_estimated' => $this->estimateLevel($user),
            'accuracy_pct' => $this->averageAccuracy($answers, $completedSessions),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function trackReport(LearningTrack $track): array
    {
        $sessions = PracticeSession::query()
            ->where('learning_track_id', $track->id)
            ->where('status', PracticeSession::STATUS_COMPLETED)
            ->get();

        $answers = Answer::query()
            ->whereIn(
                'practice_session_id',
                $sessions->pluck('id'),
            )
            ->get();

        $activeLearners = PracticeSession::query()
            ->where('learning_track_id', $track->id)
            ->where('status', PracticeSession::STATUS_COMPLETED)
            ->distinct('user_id')
            ->count('user_id');

        return [
            'track_id' => $track->id,
            'track_name' => $track->name,
            'sessions_count' => $sessions->count(),
            'avg_accuracy' => $this->averageAccuracy($answers, $sessions),
            'active_learners' => $activeLearners,
        ];
    }

    /**
     * @param  Collection<int, Answer>  $answers
     * @param  Collection<int, PracticeSession>  $completedSessions
     */
    private function globalAverageAccuracy(Collection $answers, Collection $completedSessions): float
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

        return round(($completedSessions->sum('correct_count') / $totalQuestions) * 100, 1);
    }

    /**
     * @param  Collection<int, Answer>  $answers
     * @param  Collection<int, PracticeSession>  $completedSessions
     */
    private function averageAccuracy(Collection $answers, Collection $completedSessions): float
    {
        return $this->globalAverageAccuracy($answers, $completedSessions);
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

        return collect([$fromSession, $fromAnswer])
            ->filter()
            ->map(fn ($value) => Carbon::parse($value))
            ->sortDesc()
            ->first();
    }
}
