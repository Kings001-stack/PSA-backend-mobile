<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create Super Admin account
        User::updateOrCreate(
            ['email' => 'superadmin@1.test'],
            [
                'tenant_id' => 1,
                'name' => 'Super Admin',
                'email' => 'superadmin@1.test',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'account_status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Super Admin account created successfully!');
        $this->command->info('Email: superadmin@1.test');
        $this->command->info('Password: password');
    }
}
