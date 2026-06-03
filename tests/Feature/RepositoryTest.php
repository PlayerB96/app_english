<?php

namespace Tests\Feature;

use App\Models\User;
use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\UserService;
use Tests\TestCase;

class RepositoryTest extends TestCase
{
    public function test_interface_bindings_are_resolved_from_container(): void
    {
        $baseRepository = app(RepositoryInterface::class);
        $userRepository = app(UserRepositoryInterface::class);

        $this->assertInstanceOf(UserRepositoryInterface::class, $baseRepository);
        $this->assertInstanceOf(UserRepositoryInterface::class, $userRepository);
    }

    public function test_user_service_delegates_create_to_repository(): void
    {
        $payload = [
            'name' => 'Juan Perez',
            'email' => 'juan@example.com',
            'password' => 'secret123',
        ];

        $expected = User::factory()->make(array_merge($payload, ['id' => 1]));

        $this->mock(UserRepositoryInterface::class, function ($mock) use ($payload, $expected): void {
            $mock->shouldReceive('create')
                ->once()
                ->with($payload)
                ->andReturn($expected);
        });

        $service = app(UserService::class);
        $user = $service->create($payload);

        $this->assertSame('Juan Perez', $user->name);
        $this->assertSame('juan@example.com', $user->email);
    }
}
