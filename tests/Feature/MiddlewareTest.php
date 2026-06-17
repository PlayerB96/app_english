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
        $response->assertInertia(fn ($page) => $page->component('Admin/Dashboard'));
    }

    public function test_administrator_can_access_admin_subroutes(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->actingAs($admin)->get('/admin/tracks')->assertOk();
        $this->actingAs($admin)->get('/admin/reports')->assertOk();
    }

    public function test_learner_cannot_access_admin_panel(): void
    {
        $learner = User::factory()->learner()->create();

        $response = $this->actingAs($learner)->get('/admin');

        $response->assertForbidden();
    }

    public function test_administrator_cannot_access_learner_dashboard(): void
    {
        $admin = User::factory()->administrator()->create();

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertForbidden();
    }

    public function test_learner_can_access_mock_learner_pages(): void
    {
        $learner = User::factory()->learner()->create();

        $this->actingAs($learner)->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Dashboard')->has('progress'));

        $this->actingAs($learner)->get('/practice')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Practice/Index')->has('tiers')->has('challenges'));

        $this->actingAs($learner)->get('/tracks')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Tracks/Index')->has('tiers')->has('challenges'));
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
