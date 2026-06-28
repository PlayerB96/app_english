<?php

namespace Tests\Feature;

use App\Enums\LevelProgressMode;
use App\Models\User;
use App\Models\UserLevelProgress;
use App\Services\ChallengeCatalogService;
use App\Services\LevelProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LevelProgressTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function completeTier(User $user, LevelProgressMode $mode, string $tier): void
    {
        $service = app(LevelProgressService::class);
        $catalog = app(ChallengeCatalogService::class);

        foreach ($service->levelIdsForTier($tier) as $levelId) {
            $session = $service->ensureSessionQuestions($user, $mode, $levelId);

            foreach ($session as $questionId) {
                $service->recordQuestionPass($user, $mode, $levelId, $questionId);
            }
        }
    }

    public function test_practice_page_includes_progress_from_database(): void
    {
        $learner = User::factory()->learner()->create();

        $this->actingAs($learner)->get('/practice')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Practice/Index')
                ->has('progress.unlocked', 1)
                ->where('progress.unlocked.0', 1));
    }

    public function test_world_page_shows_gate_when_locked(): void
    {
        $learner = User::factory()->learner()->create(['tokens' => 100]);

        $this->actingAs($learner)->get('/world')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('World/Index')
                ->where('world_access.unlocked', false)
                ->where('world_access.unlock_cost', 300)
                ->has('worlds', 3)
                ->has('levels', 15));
    }

    public function test_learner_can_unlock_world_with_tokens(): void
    {
        $learner = User::factory()->learner()->create(['tokens' => 350]);

        $this->actingAs($learner)
            ->post('/world/unlock')
            ->assertRedirect()
            ->assertSessionHas('status');

        $learner->refresh();
        $this->assertNotNull($learner->world_unlocked_at);
        $this->assertSame(50, $learner->tokens);
    }

    public function test_world_unlock_rejects_insufficient_tokens(): void
    {
        $learner = User::factory()->learner()->create(['tokens' => 50]);

        $this->actingAs($learner)
            ->post('/world/unlock')
            ->assertRedirect()
            ->assertSessionHasErrors('tokens');

        $this->assertNull($learner->fresh()->world_unlocked_at);
    }

    public function test_passing_all_questions_completes_level(): void
    {
        $learner = User::factory()->learner()->create();
        $service = app(LevelProgressService::class);
        $catalog = app(ChallengeCatalogService::class);

        foreach ($catalog->questionIdsForLevel(LevelProgressMode::Speaking, 1) as $questionId) {
            $service->recordQuestionPass($learner, LevelProgressMode::Speaking, 1, $questionId);
        }

        $snapshot = $service->snapshot($learner, LevelProgressMode::Speaking);

        $this->assertContains(1, $snapshot['completed']);
        $this->assertContains(2, $snapshot['unlocked']);
        $this->assertArrayNotHasKey('1', $snapshot['question_progress']);
    }

    public function test_partial_progress_is_persisted_in_database(): void
    {
        $learner = User::factory()->learner()->create();
        $service = app(LevelProgressService::class);
        $catalog = app(ChallengeCatalogService::class);
        $questionIds = $catalog->questionIdsForLevel(LevelProgressMode::Speaking, 1);

        $service->recordQuestionPass($learner, LevelProgressMode::Speaking, 1, $questionIds[0]);
        $service->recordQuestionPass($learner, LevelProgressMode::Speaking, 1, $questionIds[1]);

        $this->assertDatabaseHas('user_level_progress', [
            'user_id' => $learner->id,
            'mode' => 'speaking',
            'level_id' => 1,
            'completed_at' => null,
        ]);

        $row = UserLevelProgress::query()->first();
        $this->assertCount(2, $row?->correct_question_ids ?? []);

        $snapshot = $service->snapshot($learner, LevelProgressMode::Speaking);

        $this->assertSame(['correct' => 2, 'total' => 3], $snapshot['question_progress']['1']);
        $this->assertNotContains(1, $snapshot['completed']);
    }

    public function test_quiz_failure_locks_level_for_twenty_four_hours(): void
    {
        $learner = User::factory()->learner()->create();
        $service = app(LevelProgressService::class);

        $lockedUntil = $service->recordFail($learner, LevelProgressMode::Quiz, 1, 24);

        $this->assertTrue($lockedUntil->isFuture());
        $this->assertTrue($service->isLockedOut($learner, LevelProgressMode::Quiz, 1));
        $this->assertFalse($service->isUnlocked($learner, LevelProgressMode::Quiz, 1));

        $row = UserLevelProgress::query()->first();

        $this->assertNotNull($row?->locked_until);
    }

    public function test_learner_can_post_question_pass(): void
    {
        $learner = User::factory()->learner()->create();
        $catalog = app(ChallengeCatalogService::class);
        $questionId = $catalog->questionIdsForLevel(LevelProgressMode::Speaking, 1)[0];

        $this->actingAs($learner)
            ->post('/level-progress/speaking/question-pass', [
                'level_id' => 1,
                'question_id' => $questionId,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('user_level_progress', [
            'user_id' => $learner->id,
            'mode' => 'speaking',
            'level_id' => 1,
        ]);
    }

    public function test_learner_can_reset_tier_progress(): void
    {
        $learner = User::factory()->learner()->create(['tokens' => 100]);
        $service = app(LevelProgressService::class);

        $this->completeTier($learner, LevelProgressMode::Speaking, 'basico');

        $this->actingAs($learner)
            ->post('/level-progress/speaking/reset-tier', ['tier' => 'basico'])
            ->assertRedirect();

        foreach ($service->levelIdsForTier('basico') as $levelId) {
            $this->assertDatabaseMissing('user_level_progress', [
                'user_id' => $learner->id,
                'mode' => 'speaking',
                'level_id' => $levelId,
            ]);
        }
    }

    public function test_cannot_reset_tier_before_all_sublevels_completed(): void
    {
        $learner = User::factory()->learner()->create(['tokens' => 100]);
        $service = app(LevelProgressService::class);
        $catalog = app(ChallengeCatalogService::class);

        foreach ($catalog->questionIdsForLevel(LevelProgressMode::Speaking, 1) as $questionId) {
            $service->recordQuestionPass($learner, LevelProgressMode::Speaking, 1, $questionId);
        }

        $this->assertFalse($service->canResetTier($learner, LevelProgressMode::Speaking, 'basico'));

        $this->actingAs($learner)
            ->post('/level-progress/speaking/reset-tier', ['tier' => 'basico'])
            ->assertRedirect()
            ->assertSessionHasErrors('tier');
    }

    public function test_can_reset_tier_when_next_module_has_started_if_current_is_complete(): void
    {
        $learner = User::factory()->learner()->create(['tokens' => 100]);
        $service = app(LevelProgressService::class);
        $catalog = app(ChallengeCatalogService::class);

        $this->completeTier($learner, LevelProgressMode::Speaking, 'basico');

        $service->recordQuestionPass(
            $learner,
            LevelProgressMode::Speaking,
            6,
            $catalog->questionIdsForLevel(LevelProgressMode::Speaking, 6)[0],
        );

        $this->assertTrue($service->canResetTier($learner->fresh(), LevelProgressMode::Speaking, 'basico'));

        $this->actingAs($learner)
            ->post('/level-progress/speaking/reset-tier', ['tier' => 'basico'])
            ->assertRedirect()
            ->assertSessionHas('status');
    }

    public function test_reset_tier_spends_tokens_and_increments_count(): void
    {
        $learner = User::factory()->learner()->create(['tokens' => 100]);

        $this->completeTier($learner, LevelProgressMode::Speaking, 'basico');

        $this->actingAs($learner)
            ->post('/level-progress/speaking/reset-tier', ['tier' => 'basico'])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertSame(70, $learner->fresh()->tokens);
        $this->assertDatabaseHas('user_tier_resets', [
            'user_id' => $learner->id,
            'mode' => 'speaking',
            'tier' => 'basico',
            'reset_count' => 1,
        ]);
    }

    public function test_cannot_reset_tier_after_max_resets(): void
    {
        $learner = User::factory()->learner()->create(['tokens' => 200]);
        $service = app(LevelProgressService::class);
        $tierResets = app(\App\Services\TierResetService::class);

        $this->completeTier($learner, LevelProgressMode::Speaking, 'basico');

        for ($attempt = 0; $attempt < $tierResets->maxResets(); $attempt += 1) {
            $this->actingAs($learner)
                ->post('/level-progress/speaking/reset-tier', ['tier' => 'basico'])
                ->assertRedirect();

            $this->completeTier($learner->fresh(), LevelProgressMode::Speaking, 'basico');
        }

        $tokensBefore = $learner->fresh()->tokens;

        $this->assertFalse($service->canResetTier($learner->fresh(), LevelProgressMode::Speaking, 'basico'));

        $this->actingAs($learner)
            ->post('/level-progress/speaking/reset-tier', ['tier' => 'basico'])
            ->assertRedirect()
            ->assertSessionHasErrors('tier');

        $this->assertSame($tokensBefore, $learner->fresh()->tokens);
        $this->assertSame(
            $tierResets->maxResets(),
            $tierResets->getCount($learner->fresh(), LevelProgressMode::Speaking, 'basico'),
        );
    }

    public function test_snapshot_includes_tier_resets(): void
    {
        $learner = User::factory()->learner()->create();
        $service = app(LevelProgressService::class);

        $snapshot = $service->snapshot($learner, LevelProgressMode::Speaking);

        $this->assertArrayHasKey('tier_resets', $snapshot);
        $this->assertSame(
            ['count' => 0, 'max' => 2, 'cost' => 30],
            $snapshot['tier_resets']['basico'],
        );
        $this->assertSame(
            ['count' => 0, 'max' => 2, 'cost' => 30],
            $snapshot['tier_resets']['intermedio'],
        );
        $this->assertSame(
            ['count' => 0, 'max' => 2, 'cost' => 30],
            $snapshot['tier_resets']['avanzado'],
        );
    }

    public function test_each_level_has_nine_questions_in_pool(): void
    {
        $catalog = app(ChallengeCatalogService::class);
        $minimum = (int) config('learning.questions_pool_min', 9);

        foreach ($catalog->quizLevels() as $level) {
            $this->assertGreaterThanOrEqual(
                $minimum,
                count($level['questions']),
                "Nivel {$level['id']} quiz debe tener al menos {$minimum} preguntas en el banco.",
            );
        }
    }

    public function test_start_session_picks_new_questions_for_fresh_attempts(): void
    {
        $learner = User::factory()->learner()->create();
        $service = app(LevelProgressService::class);

        $sessions = [];

        for ($attempt = 0; $attempt < 5; $attempt += 1) {
            UserLevelProgress::query()
                ->where('user_id', $learner->id)
                ->where('mode', LevelProgressMode::Quiz->value)
                ->where('level_id', 3)
                ->delete();

            $sessions[] = implode(',', $service->ensureSessionQuestions(
                $learner->fresh(),
                LevelProgressMode::Quiz,
                3,
            ));
        }

        $this->assertGreaterThan(1, count(array_unique($sessions)));

        $active = $service->ensureSessionQuestions($learner->fresh(), LevelProgressMode::Quiz, 3);
        $service->recordQuestionPass($learner->fresh(), LevelProgressMode::Quiz, 3, $active[0]);

        $continued = $service->ensureSessionQuestions($learner->fresh(), LevelProgressMode::Quiz, 3);

        $this->assertSame($active, $continued);
    }

    public function test_learner_can_skip_lockout_with_tokens(): void
    {
        $learner = User::factory()->learner()->create(['tokens' => 100]);
        $service = app(LevelProgressService::class);

        $service->recordFail($learner, LevelProgressMode::Quiz, 1, 24);

        $this->actingAs($learner)
            ->post('/level-progress/quiz/skip-lockout', ['level_id' => 1])
            ->assertRedirect();

        $this->assertFalse($service->isLockedOut($learner->fresh(), LevelProgressMode::Quiz, 1));
        $this->assertSame(90, $learner->fresh()->tokens);
    }

    public function test_skip_lockout_rejects_insufficient_tokens(): void
    {
        $learner = User::factory()->learner()->create(['tokens' => 5]);
        $service = app(LevelProgressService::class);

        $service->recordFail($learner, LevelProgressMode::Quiz, 1, 24);

        $this->actingAs($learner)
            ->post('/level-progress/quiz/skip-lockout', ['level_id' => 1])
            ->assertRedirect()
            ->assertSessionHasErrors('tokens');

        $this->assertTrue($service->isLockedOut($learner->fresh(), LevelProgressMode::Quiz, 1));
        $this->assertSame(5, $learner->fresh()->tokens);
    }

    public function test_completed_sublevel_review_returns_user_attempts_only(): void
    {
        $learner = User::factory()->learner()->create();

        $this->actingAs($learner)
            ->post('/level-progress/speaking/start-session', ['level_id' => 1])
            ->assertRedirect();

        $row = UserLevelProgress::query()->first();
        $sessionIds = $row?->session_question_ids ?? [];

        $this->assertCount(3, $sessionIds);

        $this->actingAs($learner)
            ->post('/level-progress/speaking/attempt', [
                'level_id' => 1,
                'question_id' => $sessionIds[0],
                'is_correct' => false,
                'response_text' => 'wrong answer',
                'input_mode' => 'voice',
            ])
            ->assertRedirect();

        foreach ($sessionIds as $questionId) {
            $this->actingAs($learner)
                ->post('/level-progress/speaking/question-pass', [
                    'level_id' => 1,
                    'question_id' => $questionId,
                    'response_text' => 'correct answer',
                    'input_mode' => 'voice',
                ])
                ->assertRedirect();
        }

        $this->actingAs($learner)
            ->getJson('/level-progress/speaking/levels/1/review')
            ->assertOk()
            ->assertJsonPath('summary.total', 3)
            ->assertJsonPath('summary.correct', 3)
            ->assertJsonPath('summary.incorrect_attempts', 1)
            ->assertJsonCount(3, 'questions')
            ->assertJsonPath('questions.0.attempts.0.response_text', 'wrong answer')
            ->assertJsonPath('questions.0.attempts.0.is_correct', false);
    }
}
