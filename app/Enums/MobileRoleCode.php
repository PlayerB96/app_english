<?php

namespace App\Enums;

enum MobileRoleCode: string
{
    case Administrador = '00001';

    case CajaRapida = '00005';

    /**
     * @return list<string>
     */
    public static function loginAllowedCodes(): array
    {
        return [
            self::CajaRapida->value,
            self::Administrador->value,
        ];
    }
}
