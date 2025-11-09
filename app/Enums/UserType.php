<?php

namespace App\Enums;

enum UserType: string
{
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case SALES_REP = 'sales_rep';
    case CLIENT = 'client';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
