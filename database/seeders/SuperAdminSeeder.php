<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'username' => 'superadmin',
            'email' => 'admin@organization.com',
            'password' => Hash::make('SuperAdmin123!'),
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'user_role_id' => UserRole::SUPER_ADMIN->value,
            'is_active' => true,
            'organization_id' => null,
        ]);
    }
}
