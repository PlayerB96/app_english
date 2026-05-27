<?php

namespace Tests\Feature;

use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_interface_bindings_are_resolved_from_container(): void
    {
        $baseRepository = app(RepositoryInterface::class);
        $userRepository = app(UserRepositoryInterface::class);

        $this->assertInstanceOf(UserRepositoryInterface::class, $baseRepository);
        $this->assertInstanceOf(UserRepositoryInterface::class, $userRepository);
    }

    public function test_user_service_uses_repository_to_create_records(): void
    {
        $service = app(UserService::class);

        $user = $service->create([
            'name' => 'Juan Perez',
            'email' => 'juan@example.com',
            'password' => 'secret123',
        ]);

        $this->assertDatabaseHas('users', ['email' => 'juan@example.com']);
        $this->assertSame('Juan Perez', $user->name);
    }
}
