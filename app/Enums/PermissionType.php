<?php

namespace App\Enums;

enum PermissionType: int
{
    case CREATE = 1;
    case READ = 2;
    case UPDATE = 3;
    case DELETE = 4;
    case DOWNLOAD = 5;

    public function label(): string
    {
        return match($this) {
            self::CREATE => 'Crear',
            self::READ => 'Leer',
            self::UPDATE => 'Actualizar',
            self::DELETE => 'Eliminar',
            self::DOWNLOAD => 'Descargar',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
