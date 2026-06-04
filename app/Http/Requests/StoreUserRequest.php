<?php

namespace App\Http\Requests;

/**
 * @internal Plantilla WS-002 — solo tests de patrón (RequestTest). No usar en producción.
 */
class StoreUserRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }
}
