<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('login');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function postLogin(array $data): TestResponse
    {
        return $this->withoutMiddleware(ThrottleRequests::class)
            ->from('/login')
            ->postWithCsrf('/login', $data);
    }

    public function test_guest_can_view_login_page(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
    }

    public function test_login_page_shows_dev_accounts_in_local_debug_mode(): void
    {
        config(['app.debug' => true]);

        $response = $this->get('/login');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Auth/Login')
            ->where('showDevAccounts', true));
    }

    public function test_learner_can_login_with_valid_credentials(): void
    {
        User::factory()->learner()->create([
            'email' => 'learner@app-english.test',
            'password' => 'password',
        ]);

        $response = $this->postLogin([
            'email' => 'learner@app-english.test',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs(User::query()->first());
    }

    public function test_administrator_can_login_with_valid_credentials(): void
    {
        User::factory()->administrator()->create([
            'email' => 'admin@app-english.test',
            'password' => 'password',
        ]);

        $response = $this->postLogin([
            'email' => 'admin@app-english.test',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));

        $user = auth()->user();
        $this->assertInstanceOf(User::class, $user);
        $this->assertTrue($user->isAdministrator());
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->learner()->create([
            'email' => 'learner@app-english.test',
            'password' => 'password',
        ]);

        $response = $this->postLogin([
            'email' => 'learner@app-english.test',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->learner()->create();

        $response = $this->actingAs($user)->postWithCsrf('/logout');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_guest_is_redirected_to_login_when_accessing_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login'));
    }

    public function test_login_sanitizes_email_input(): void
    {
        User::factory()->learner()->create([
            'email' => 'learner@app-english.test',
            'password' => 'password',
        ]);

        $response = $this->postLogin([
            'email' => '  LEARNER@app-english.test  ',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }
}
