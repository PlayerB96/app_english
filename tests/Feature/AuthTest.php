<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
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

        $response->assertRedirect(route('admin.dashboard'));

        $user = auth()->user();
        $this->assertInstanceOf(User::class, $user);
        $this->assertTrue($user->isAdministrator());
    }

    public function test_login_fails_with_invalid_password(): void
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
        $response->assertSessionHasErrors([
            'password' => 'La contraseña es incorrecta.',
        ]);
        $response->assertSessionDoesntHaveErrors('email');
        $this->assertGuest();
    }

    public function test_login_fails_with_unknown_email(): void
    {
        $response = $this->postLogin([
            'email' => 'missing@app-english.test',
            'password' => 'password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors([
            'email' => 'No existe una cuenta registrada con este correo.',
        ]);
        $response->assertSessionDoesntHaveErrors('password');
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

    /**
     * @param  array<string, mixed>  $data
     */
    private function postRegister(array $data): TestResponse
    {
        return $this->withoutMiddleware(ThrottleRequests::class)
            ->from('/register')
            ->postWithCsrf('/register', $data);
    }

    public function test_guest_can_view_register_page(): void
    {
        $response = $this->get('/register');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Auth/Register'));
    }

    public function test_user_can_register_and_receives_verification_email(): void
    {
        Notification::fake();

        $response = $this->postRegister([
            'name' => 'Nuevo Usuario',
            'email' => 'nuevo@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'email' => 'nuevo@example.com',
            'name' => 'Nuevo Usuario',
            'role' => UserRole::Learner->value,
            'tokens' => 100,
            'email_verified_at' => null,
        ]);

        $user = User::query()->where('email', 'nuevo@example.com')->first();
        $this->assertInstanceOf(User::class, $user);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_unverified_user_cannot_access_dashboard(): void
    {
        $user = User::factory()->learner()->unverified()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('verification.notice'));
    }

    public function test_user_can_verify_email_via_signed_link(): void
    {
        $user = User::factory()->learner()->unverified()->create([
            'email' => 'verify@example.com',
        ]);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHour(),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ],
        );

        $response = $this->get($url);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user->fresh());
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_register_rejects_duplicate_email(): void
    {
        User::factory()->learner()->create([
            'email' => 'duplicado@example.com',
        ]);

        $response = $this->postRegister([
            'name' => 'Otro Usuario',
            'email' => 'duplicado@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
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
