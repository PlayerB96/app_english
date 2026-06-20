<?php

namespace Tests\Feature;

use App\Models\User;
use App\Repositories\Contracts\AuthRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticate_returns_user_with_valid_credentials(): void
    {
        User::factory()->learner()->create([
            'email' => 'learner@app-english.test',
            'password' => 'password',
        ]);

        $repository = app(AuthRepositoryInterface::class);

        $user = $repository->authenticate('learner@app-english.test', 'password');

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('learner@app-english.test', $user->email);
    }

    public function test_authenticate_returns_null_with_invalid_password(): void
    {
        User::factory()->learner()->create([
            'email' => 'learner@app-english.test',
            'password' => 'password',
        ]);

        $repository = app(AuthRepositoryInterface::class);

        $this->assertNull($repository->authenticate('learner@app-english.test', 'wrong'));
    }

    public function test_authenticate_returns_null_when_user_does_not_exist(): void
    {
        $repository = app(AuthRepositoryInterface::class);

        $this->assertNull($repository->authenticate('missing@app-english.test', 'password'));
    }

    public function test_create_user_persists_learner_with_unverified_email(): void
    {
        $repository = app(AuthRepositoryInterface::class);

        $user = $repository->createUser('Test User', 'test@example.com', 'password123');

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
        $this->assertTrue($user->isLearner());
        $this->assertSame(100, $user->tokens);
    }
}
