<?php

namespace App\Enums;

enum UserRole: string
{
    case Cashier = 'cashier';
    case Administrator = 'administrator';
}
