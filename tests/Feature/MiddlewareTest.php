<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_access_admin_panel(): void
    {
        $admin = User::factory()->administrator()->create();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
    }

    public function test_learner_cannot_access_admin_panel(): void
    {
        $learner = User::factory()->learner()->create();

        $response = $this->actingAs($learner)->get('/admin');

        $response->assertForbidden();
    }

    public function test_guest_cannot_access_admin_panel(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect(route('login'));
    }

    public function test_user_exposes_expected_role(): void
    {
        $learner = User::factory()->learner()->create();
        $admin = User::factory()->administrator()->create();

        $this->assertSame(UserRole::Learner, $learner->role);
        $this->assertSame(UserRole::Administrator, $admin->role);
    }
}
