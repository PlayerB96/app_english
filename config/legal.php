<?php

return [

    'business' => [
        'name' => env('LEGAL_BUSINESS_NAME', env('APP_NAME', 'Dev English')),
        'ruc' => env('LEGAL_RUC', '00000000000'),
        'address' => env('LEGAL_ADDRESS', 'Lima, Perú'),
        'email' => env('LEGAL_EMAIL', 'contacto@example.com'),
        'phone' => env('LEGAL_PHONE'),
    ],

    /**
     * Plazo máximo de respuesta al consumidor (días calendario).
     * Servicios digitales: 15 días según normativa de protección al consumidor.
     */
    'complaint_response_days' => (int) env('LEGAL_COMPLAINT_RESPONSE_DAYS', 15),

];
