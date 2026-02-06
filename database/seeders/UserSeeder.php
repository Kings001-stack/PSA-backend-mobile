<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            // Create admin user
            User::create([
                'tenant_id' => $tenant->id,
                'name' => 'Admin User',
                'email' => "admin@{$tenant->id}.test",
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]);

            // Create pharmacist user
            User::create([
                'tenant_id' => $tenant->id,
                'name' => 'Pharmacist User',
                'email' => "pharmacist@{$tenant->id}.test",
                'password' => Hash::make('password'),
                'role' => 'pharmacist',
            ]);

            // Create staff user
            User::create([
                'tenant_id' => $tenant->id,
                'name' => 'Staff User',
                'email' => "staff@{$tenant->id}.test",
                'password' => Hash::make('password'),
                'role' => 'staff',
            ]);
        }

        $this->command->info('Created users for all tenants');
    }
}
