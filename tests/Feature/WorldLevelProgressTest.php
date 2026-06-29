<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserWorldLevelProgress;
use App\Services\WorldLevelProgressService;
use App\Services\WorldQuestionCatalogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorldLevelProgressTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function unlockWorld(User $user): User
    {
        $user->forceFill([
            'world_unlocked_at' => now(),
            'tokens' => 500,
        ])->save();

        return $user->fresh();
    }

    public function test_world_session_endpoint_starts_quiz_session(): void
    {
        $learner = $this->unlockWorld(User::factory()->learner()->create());

        $this->actingAs($learner)
            ->post('/world/levels/1/session')
            ->assertRedirect();

        $row = UserWorldLevelProgress::query()
            ->where('user_id', $learner->id)
            ->where('level_id', 1)
            ->first();

        $this->assertNotNull($row);
        $this->assertCount(3, $row->session_question_ids ?? []);
    }

    public function test_passing_all_world_questions_completes_level_and_unlocks_next(): void
    {
        $learner = $this->unlockWorld(User::factory()->learner()->create());
        $service = app(WorldLevelProgressService::class);
        $catalog = app(WorldQuestionCatalogService::class);

        $session = $service->ensureSessionQuestions($learner, 1);

        foreach ($session as $questionId) {
            $result = $service->recordQuestionPass($learner, 1, $questionId);
        }

        $this->assertTrue($result['completed']);
        $this->assertContains(1, app(\App\Services\WorldProgressService::class)->snapshot($learner)['completed']);
        $this->assertContains(2, app(\App\Services\WorldProgressService::class)->snapshot($learner)['unlocked']);
    }

    public function test_world_quiz_failure_locks_level_for_twenty_four_hours(): void
    {
        $learner = $this->unlockWorld(User::factory()->learner()->create());
        $service = app(WorldLevelProgressService::class);

        $lockedUntil = $service->recordFail($learner, 1, 2);

        $this->assertTrue($lockedUntil->isFuture());
        $this->assertTrue($service->isLockedOut($learner, 1));

        $this->actingAs($learner)
            ->post('/world/levels/1/fail', ['hours' => 2])
            ->assertRedirect();

        $row = UserWorldLevelProgress::query()
            ->where('user_id', $learner->id)
            ->where('level_id', 1)
            ->first();

        $this->assertNotNull($row?->locked_until);
        $this->assertNull($row->session_question_ids);
        $this->assertSame([], $row->correct_question_ids ?? []);
    }

    public function test_world_session_rejects_locked_level(): void
    {
        $learner = $this->unlockWorld(User::factory()->learner()->create());
        $service = app(WorldLevelProgressService::class);
        $service->recordFail($learner, 1, 2);

        $this->actingAs($learner)
            ->post('/world/levels/1/session')
            ->assertRedirect()
            ->assertSessionHasErrors('level_id');
    }

    public function test_world_page_includes_quiz_progress_when_unlocked(): void
    {
        $learner = $this->unlockWorld(User::factory()->learner()->create());

        $this->actingAs($learner)->get('/world')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('World/Index')
                ->where('world_access.unlocked', true)
                ->has('progress.unlocked', 1)
                ->where('progress.unlocked.0', 1)
                ->has('progress.lockouts')
                ->has('progress.session_questions')
                ->has('questions_by_level.1', 3));
    }

    public function test_world_answer_endpoint_records_question_pass(): void
    {
        $learner = $this->unlockWorld(User::factory()->learner()->create());
        $catalog = app(WorldQuestionCatalogService::class);
        $service = app(WorldLevelProgressService::class);
        $session = $service->ensureSessionQuestions($learner, 1);
        $questionId = $session[0];

        $this->actingAs($learner)
            ->post('/world/levels/1/answer', [
                'question_id' => $questionId,
                'response_text' => 'test',
                'input_mode' => 'choice',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('user_world_level_progress', [
            'user_id' => $learner->id,
            'level_id' => 1,
            'completed_at' => null,
        ]);

        $row = UserWorldLevelProgress::query()->first();
        $this->assertContains($questionId, $row?->correct_question_ids ?? []);
    }
}
