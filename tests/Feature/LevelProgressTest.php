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
        $learner = User::factory()->learner()->create();
        $service = app(LevelProgressService::class);
        $catalog = app(ChallengeCatalogService::class);

        foreach ($catalog->questionIdsForLevel(LevelProgressMode::Speaking, 1) as $questionId) {
            $service->recordQuestionPass($learner, LevelProgressMode::Speaking, 1, $questionId);
        }

        $service->recordQuestionPass(
            $learner,
            LevelProgressMode::Speaking,
            2,
            $catalog->questionIdsForLevel(LevelProgressMode::Speaking, 2)[0],
        );

        $this->actingAs($learner)
            ->post('/level-progress/speaking/reset-tier', ['tier' => 'basico'])
            ->assertRedirect();

        $this->assertDatabaseMissing('user_level_progress', [
            'user_id' => $learner->id,
            'mode' => 'speaking',
            'level_id' => 1,
        ]);

        $this->assertDatabaseMissing('user_level_progress', [
            'user_id' => $learner->id,
            'mode' => 'speaking',
            'level_id' => 2,
        ]);
    }

    public function test_cannot_reset_tier_when_next_module_has_started(): void
    {
        $learner = User::factory()->learner()->create();
        $service = app(LevelProgressService::class);
        $catalog = app(ChallengeCatalogService::class);

        foreach ($catalog->questionIdsForLevel(LevelProgressMode::Speaking, 1) as $questionId) {
            $service->recordQuestionPass($learner, LevelProgressMode::Speaking, 1, $questionId);
        }

        UserLevelProgress::query()->create([
            'user_id' => $learner->id,
            'mode' => LevelProgressMode::Speaking->value,
            'level_id' => 6,
            'correct_question_ids' => [
                $catalog->questionIdsForLevel(LevelProgressMode::Speaking, 6)[0],
            ],
        ]);

        $this->assertFalse($service->canResetTier($learner, LevelProgressMode::Speaking, 'basico'));
        $this->assertTrue($service->canResetTier($learner, LevelProgressMode::Speaking, 'intermedio'));

        $this->actingAs($learner)
            ->post('/level-progress/speaking/reset-tier', ['tier' => 'basico'])
            ->assertRedirect()
            ->assertSessionHasErrors('tier');

        $this->assertDatabaseHas('user_level_progress', [
            'user_id' => $learner->id,
            'mode' => 'speaking',
            'level_id' => 1,
        ]);
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
}
