<?php

namespace App\Enums;

enum TenancyMode: string
{
    case SHARED = 'shared';
    case DEDICATED = 'dedicated';

    public function label(): string
    {
        return match($this) {
            self::SHARED => 'Compartido',
            self::DEDICATED => 'Dedicado',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::SHARED => 'Módulos que utilizan la misma base de datos sin tenencia',
            self::DEDICATED => 'Módulos con base de datos dedicada por empresa',
        };
    }
}
