<?php

namespace Tests\Feature;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_repository_creates_records_via_base_repository(): void
    {
        $repository = new UserRepository(new User());

        $user = $repository->create([
            'name' => 'Juan Perez',
            'email' => 'juan@example.com',
            'password' => 'secret123',
        ]);

        $this->assertDatabaseHas('users', ['email' => 'juan@example.com']);
        $this->assertSame('Juan Perez', $user->name);
    }
}
