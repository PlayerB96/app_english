<?php

namespace App\Http\Requests;

class RedeemPowerCodeRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'regex:/^[A-Za-z0-9]{3}$/'],
        ];
    }

    public function attributes(): array
    {
        return [
            ...parent::attributes(),
            'code' => 'código',
        ];
    }

    public function messages(): array
    {
        return [
            ...parent::messages(),
            'code.regex' => 'El código debe tener 3 caracteres (letras o números).',
        ];
    }
}
