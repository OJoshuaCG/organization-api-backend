<?php

namespace Database\Seeders;

use App\Enums\PermissionType;
use App\Enums\TenancyMode;
use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        // User Roles
        $roles = [
            ['id' => UserRole::SUPER_ADMIN->value, 'name' => 'Super Administrador', 'description' => 'Control total del sistema', 'whmcs_login' => false],
            ['id' => UserRole::ADMIN->value, 'name' => 'Administrador', 'description' => 'Gestión de organizaciones y configuración', 'whmcs_login' => false],
            ['id' => UserRole::ORGANIZATION_OWNER->value, 'name' => 'Dueño de Organización', 'description' => 'Gestión de su organización y empresas', 'whmcs_login' => true],
            ['id' => UserRole::COMPANY_OWNER->value, 'name' => 'Dueño de Empresa', 'description' => 'Gestión de su empresa', 'whmcs_login' => true],
            ['id' => UserRole::USER->value, 'name' => 'Usuario', 'description' => 'Usuario estándar con acceso limitado', 'whmcs_login' => true],
        ];
        DB::table('cat_user_roles')->insert($roles);

        // Permission Types
        $permissions = [
            ['id' => PermissionType::CREATE->value, 'permission' => 'create'],
            ['id' => PermissionType::READ->value, 'permission' => 'read'],
            ['id' => PermissionType::UPDATE->value, 'permission' => 'update'],
            ['id' => PermissionType::DELETE->value, 'permission' => 'delete'],
            ['id' => PermissionType::DOWNLOAD->value, 'permission' => 'download'],
        ];
        DB::table('cat_permission_types')->insert($permissions);

        // Tenancy Mode Groups
        $tenancyGroups = [
            ['name' => 'Default Shared', 'description' => 'Conexiones compartidas por defecto', 'tenancy_mode' => TenancyMode::SHARED->value],
            ['name' => 'Default Dedicated', 'description' => 'Conexiones dedicadas por defecto', 'tenancy_mode' => TenancyMode::DEDICATED->value],
        ];
        DB::table('tenancy_mode_groups')->insert($tenancyGroups);
    }
}
