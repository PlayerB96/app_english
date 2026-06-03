<?php

namespace Tests\Feature;

use App\Http\Requests\StoreUserRequest;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RequestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::post('/_test/store-user', function (StoreUserRequest $request) {
            return response()->json(['ok' => true, 'data' => $request->validated()]);
        });
    }

    public function test_base_form_request_returns_messages_in_spanish(): void
    {
        $response = $this->postJson('/_test/store-user', []);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Error de validacion.')
            ->assertJsonPath('errors.name.0', 'El campo nombre es obligatorio.')
            ->assertJsonPath('errors.email.0', 'El campo correo electrónico es obligatorio.')
            ->assertJsonPath('errors.password.0', 'El campo contraseña es obligatorio.');
    }
}
