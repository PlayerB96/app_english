<?php

namespace Tests\Feature;

use App\Enums\LevelProgressMode;
use App\Models\Answer;
use App\Models\LearningTrack;
use App\Models\PracticeSession;
use App\Models\ProgressSnapshot;
use App\Models\User;
use App\Services\ChallengeCatalogService;
use App\Services\LevelProgressService;
use App\Services\ProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgressDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_dashboard_shows_real_empty_metrics_for_new_learner(): void
    {
        $learner = User::factory()->learner()->create();

        $this->actingAs($learner)
            ->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Dashboard')
                ->where('progress.summary.total_sessions', 0)
                ->where('progress.summary.avg_accuracy', 0)
                ->where('progress.summary.streak_days', 0)
                ->where('progress.recent_sessions', []));
    }

    public function test_question_pass_persists_session_answer_and_snapshot_on_sublevel_complete(): void
    {
        $learner = User::factory()->learner()->create();
        $levelProgress = app(LevelProgressService::class);
        $progress = app(ProgressService::class);
        $catalog = app(ChallengeCatalogService::class);

        foreach ($catalog->questionIdsForLevel(LevelProgressMode::Quiz, 1) as $index => $questionId) {
            $levelProgress->recordQuestionPass($learner, LevelProgressMode::Quiz, 1, $questionId);

            $progress->recordQuestionAttempt(
                $learner,
                LevelProgressMode::Quiz,
                $questionId,
                1,
                true,
                'respuesta '.$index,
                'choice',
                $index === 2,
            );
        }

        $this->assertDatabaseCount('answers', 3);
        $this->assertDatabaseHas('practice_sessions', [
            'user_id' => $learner->id,
            'status' => PracticeSession::STATUS_COMPLETED,
            'question_count' => 3,
            'correct_count' => 3,
        ]);
        $this->assertDatabaseCount('progress_snapshots', 1);

        $this->actingAs($learner)
            ->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('progress.summary.total_sessions', 1)
                ->where('progress.summary.avg_accuracy', 100)
                ->has('progress.recent_sessions', 1)
                ->has('progress.chart_points', 1));
    }

    public function test_quiz_fail_records_incorrect_answer_and_closes_session(): void
    {
        $learner = User::factory()->learner()->create();
        $catalog = app(ChallengeCatalogService::class);
        $questionId = $catalog->questionIdsForLevel(LevelProgressMode::Quiz, 1)[0];

        $this->actingAs($learner)
            ->post('/level-progress/quiz/fail', [
                'level_id' => 1,
                'question_id' => $questionId,
                'response_text' => 'wrong',
                'input_mode' => 'choice',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('answers', [
            'user_id' => $learner->id,
            'question_id' => $questionId,
            'is_correct' => false,
        ]);

        $this->assertDatabaseHas('practice_sessions', [
            'user_id' => $learner->id,
            'status' => PracticeSession::STATUS_COMPLETED,
            'question_count' => 1,
            'correct_count' => 0,
        ]);

        $this->assertSame(1, ProgressSnapshot::query()->count());
    }

    public function test_dashboard_uses_learning_track_name_from_database(): void
    {
        $learner = User::factory()->learner()->create();
        $track = LearningTrack::query()
            ->where('slug', config('learning.tracks.speaking.slug'))
            ->firstOrFail();

        PracticeSession::query()->create([
            'user_id' => $learner->id,
            'learning_track_id' => $track->id,
            'status' => PracticeSession::STATUS_COMPLETED,
            'started_at' => now()->subHour(),
            'completed_at' => now(),
            'question_count' => 2,
            'correct_count' => 1,
        ]);

        Answer::query()->create([
            'question_id' => $track->questions()->first()->id,
            'user_id' => $learner->id,
            'practice_session_id' => PracticeSession::query()->first()->id,
            'response_text' => 'hello',
            'is_correct' => true,
            'input_mode' => 'voice',
            'evaluated_at' => now(),
        ]);

        $this->actingAs($learner)
            ->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('progress.summary.suggested_track_name', $track->name)
                ->where('progress.summary.total_sessions', 1));
    }
}
