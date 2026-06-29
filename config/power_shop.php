<?php

return [

    /**
     * Paquetes de poder disponibles para compra.
     * 100 poder = S/ 10 (S/ 0.10 por unidad).
     *
     * @var list<array{power: int, soles: int}>
     */
    'packages' => [
        ['power' => 100, 'soles' => 10],
        ['power' => 300, 'soles' => 30],
        ['power' => 500, 'soles' => 50],
    ],

    'yape' => [
        'phone' => env('POWER_SHOP_YAPE_PHONE', '999 999 999'),
        'holder' => env('POWER_SHOP_YAPE_HOLDER', 'Dev English'),
        'qr_image' => env('POWER_SHOP_YAPE_QR', '/images/payments/yape-qr.svg'),
    ],

    'plin' => [
        'phone' => env('POWER_SHOP_PLIN_PHONE', '999 999 999'),
        'holder' => env('POWER_SHOP_PLIN_HOLDER', 'Dev English'),
        'qr_image' => env('POWER_SHOP_PLIN_QR', '/images/payments/plin-qr.svg'),
    ],

    /**
     * Códigos promocionales de canje (clave interna => definición).
     * El usuario ingresa public_code (3 caracteres alfanuméricos); el backend resuelve internal.
     *
     * @var array<string, array{public_code: string, power: int}>
     */
    'redeem_codes' => [
        'LN1' => [
            'public_code' => env('POWER_REDEEM_LN1_PUBLIC', 'LN1'),
            'power' => 500,
        ],
    ],

];
