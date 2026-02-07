<?php

namespace App\Enums;

enum UserRole: int
{
    case SUPER_ADMIN = 1;
    case ADMIN = 2;
    case ORGANIZATION_OWNER = 3;
    case COMPANY_OWNER = 4;
    case USER = 5;

    public function label(): string
    {
        return match($this) {
            self::SUPER_ADMIN => 'Super Administrador',
            self::ADMIN => 'Administrador',
            self::ORGANIZATION_OWNER => 'Dueño de Organización',
            self::COMPANY_OWNER => 'Dueño de Empresa',
            self::USER => 'Usuario',
        };
    }

    public function isAdmin(): bool
    {
        return in_array($this, [self::SUPER_ADMIN, self::ADMIN]);
    }

    public function isOrganizationLevel(): bool
    {
        return in_array($this, [self::SUPER_ADMIN, self::ADMIN, self::ORGANIZATION_OWNER]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
