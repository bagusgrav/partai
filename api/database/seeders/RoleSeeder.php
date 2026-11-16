<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['super_admin', 'pengurus', 'panelis'] as $name) {
            Role::query()->firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }

        if (app()->environment('local')) {
            $admin = User::query()->firstOrCreate(
                ['email' => 'admin@partai.id'],
                [
                    'name' => 'Super Admin',
                    'password' => env('PARTAI_SUPER_ADMIN_PASSWORD', 'password'),
                ],
            );
            $admin->assignRole('super_admin');
        }
    }
}
