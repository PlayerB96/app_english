<?php

namespace Tests\Feature;

use App\Http\Resources\UserResource;
use App\Models\User;
use Tests\TestCase;

class ResourceTest extends TestCase
{
    public function test_user_resource_returns_consistent_json_structure(): void
    {
        $user = User::factory()->make([
            'id' => 1,
            'name' => 'Maria',
            'email' => 'maria@example.com',
        ]);

        $resource = UserResource::make($user)->response()->getData(true);

        $this->assertSame($user->id, $resource['data']['id']);
        $this->assertSame('User', $resource['data']['type']);
        $this->assertSame('Maria', $resource['data']['attributes']['name']);
        $this->assertSame('maria@example.com', $resource['data']['attributes']['email']);
        $this->assertSame('cashier', $resource['data']['attributes']['role']);
    }
}
