<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = [
            [
                'name' => 'HealthPlus Pharmacy',
                'email' => 'admin@healthplus.com',
                'phone' => '+1-555-0101',
                'tenant_token' => \Illuminate\Support\Str::random(64),
                'pinecone_namespace' => 'tenant_'.\Illuminate\Support\Str::uuid(),
                'is_active' => true,
                'settings' => [
                    'business_hours' => '9:00 AM - 9:00 PM',
                    'timezone' => 'America/New_York',
                ],
            ],
            [
                'name' => 'MediCare Pharmacy',
                'email' => 'admin@medicare.com',
                'phone' => '+1-555-0102',
                'tenant_token' => \Illuminate\Support\Str::random(64),
                'pinecone_namespace' => 'tenant_'.\Illuminate\Support\Str::uuid(),
                'is_active' => true,
                'settings' => [
                    'business_hours' => '8:00 AM - 10:00 PM',
                    'timezone' => 'America/Los_Angeles',
                ],
            ],
        ];

        foreach ($tenants as $tenantData) {
            Tenant::create($tenantData);
        }

        $this->command->info('Created '.count($tenants).' sample tenants');
    }
}
