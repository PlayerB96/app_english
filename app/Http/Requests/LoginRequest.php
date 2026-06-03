<?php

namespace App\Http\Requests;

class LoginRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'max:15'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'username' => trim((string) $this->input('username', '')),
        ]);
    }
}
