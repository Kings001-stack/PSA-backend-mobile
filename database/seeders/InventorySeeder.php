<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Medication;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenant = Tenant::first();
        
        if (!$tenant) {
            $this->command->error('No tenant found. Run MedicationSeeder first.');
            return;
        }

        $medications = Medication::where('tenant_id', $tenant->id)->get();

        if ($medications->isEmpty()) {
            $this->command->error('No medications found. Run MedicationSeeder first.');
            return;
        }

        $index = 0;
        foreach ($medications as $medication) {
            // Generate random but realistic inventory quantities
            $quantity = rand(50, 500);
            $reorderLevel = rand(20, 50);
            $expiryDate = now()->addMonths(rand(6, 24));
            
            // Force a few low stock and expiring alerts for realistic dashboard testing
            if ($index === 0) {
                $quantity = 5;
                $reorderLevel = 30; // Low stock
            } else if ($index === 1) {
                $expiryDate = now()->addDays(rand(5, 15)); // Expiring soon
            } else if ($index === 2) {
                $quantity = 8;
                $reorderLevel = 25; // Low stock
                $expiryDate = now()->addDays(rand(2, 8)); // Expiring soon
            }
            
            Inventory::create([
                'tenant_id' => $tenant->id,
                'medication_id' => $medication->id,
                'quantity' => $quantity,
                'reorder_level' => $reorderLevel,
                'expiry_date' => $expiryDate,
                'batch_number' => 'BATCH-' . strtoupper(substr(md5($medication->name), 0, 8)),
                'supplier' => $this->getRandomSupplier(),
                'cost_price' => $medication->price * 0.6, // 40% markup
            ]);
            
            $index++;
        }

        $this->command->info('✓ Seeded inventory for ' . $medications->count() . ' medications');
    }

    private function getRandomSupplier(): string
    {
        $suppliers = [
            'MedSupply Co.',
            'PharmaDirect Ltd.',
            'HealthCare Distributors',
            'Global Pharma Solutions',
            'MediSource Inc.',
        ];

        return $suppliers[array_rand($suppliers)];
    }
}
