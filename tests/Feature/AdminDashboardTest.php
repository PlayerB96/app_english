<?php

namespace Tests\Feature;

use App\Enums\LevelProgressMode;
use App\Enums\UserRole;
use App\Models\LearningTrack;
use App\Models\PracticeSession;
use App\Models\User;
use App\Services\ChallengeCatalogService;
use App\Services\LevelProgressService;
use App\Services\ProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_admin_dashboard_uses_real_metrics(): void
    {
        $admin = User::factory()->administrator()->create();
        $learner = User::factory()->learner()->create(['name' => 'Learner Real']);

        $catalog = app(ChallengeCatalogService::class);
        $levelProgress = app(LevelProgressService::class);
        $progress = app(ProgressService::class);

        $questionId = $catalog->questionIdsForLevel(LevelProgressMode::Quiz, 1)[0];
        $levelProgress->recordQuestionPass($learner, LevelProgressMode::Quiz, 1, $questionId);
        $progress->recordQuestionAttempt(
            $learner,
            LevelProgressMode::Quiz,
            $questionId,
            1,
            true,
            'answer',
            'choice',
            true,
        );

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Dashboard')
                ->where('dashboard.kpis.total_learners', 2)
                ->where('dashboard.kpis.completed_sessions', 1)
                ->has('dashboard.recent_learners', 1)
                ->where('dashboard.recent_learners.0.name', 'Learner Real'));
    }

    public function test_admin_users_lists_learners_from_database(): void
    {
        $admin = User::factory()->administrator()->create();
        User::factory()->learner()->create(['email' => 'learner@test.com']);

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Users/Index')
                ->has('learners', 2)
                ->where('learners.0.email', 'learner@test.com'));
    }

    public function test_admin_tracks_lists_database_tracks_with_session_counts(): void
    {
        $admin = User::factory()->administrator()->create();
        $learner = User::factory()->learner()->create();
        $track = LearningTrack::query()
            ->where('slug', config('learning.tracks.quiz.slug'))
            ->firstOrFail();

        PracticeSession::query()->create([
            'user_id' => $learner->id,
            'learning_track_id' => $track->id,
            'status' => PracticeSession::STATUS_COMPLETED,
            'started_at' => now()->subHour(),
            'completed_at' => now(),
            'question_count' => 1,
            'correct_count' => 1,
        ]);

        $this->actingAs($admin)
            ->get('/admin/tracks')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Tracks/Index')
                ->has('tracks', 2)
                ->where('tracks.1.session_count', 1));
    }

    public function test_admin_can_update_track_in_database(): void
    {
        $admin = User::factory()->administrator()->create();
        $track = LearningTrack::query()->firstOrFail();

        $this->actingAs($admin)
            ->patch("/admin/tracks/{$track->id}", [
                'name' => 'Track actualizado',
                'is_active' => false,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('learning_tracks', [
            'id' => $track->id,
            'name' => 'Track actualizado',
            'is_active' => false,
        ]);
    }

    public function test_learner_cannot_access_admin_routes(): void
    {
        $learner = User::factory()->learner()->create();

        $this->actingAs($learner)->get('/admin')->assertForbidden();
    }
}
