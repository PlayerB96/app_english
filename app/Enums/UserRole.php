<?php

namespace App\Enums;

enum UserRole: string
{
    case Learner = 'learner';
    case Administrator = 'administrator';
}
