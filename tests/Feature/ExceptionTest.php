<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

class ExceptionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/_test/runtime-exception', function () {
            throw new RuntimeException('Fallo de prueba');
        });

        Route::get('/_test/model-not-found', function () {
            throw (new ModelNotFoundException)->setModel(User::class);
        });
    }

    public function test_global_handler_returns_consistent_json_for_server_errors(): void
    {
        $response = $this->getJson('/_test/runtime-exception');

        $response->assertStatus(500)
            ->assertJsonPath('message', 'Error interno del servidor.');
    }

    public function test_global_handler_returns_consistent_json_for_not_found_errors(): void
    {
        $response = $this->getJson('/_test/model-not-found');

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Recurso no encontrado.');
    }
}
