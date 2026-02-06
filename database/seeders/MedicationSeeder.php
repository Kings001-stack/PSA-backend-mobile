<?php

namespace Database\Seeders;

use App\Models\Medication;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class MedicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $medications = [
            [
                'name' => 'Aspirin',
                'generic_name' => 'Acetylsalicylic Acid',
                'description' => 'Pain reliever and fever reducer',
                'dosage_form' => 'Tablet',
                'strength' => '325mg',
                'usage_instructions' => 'Take 1-2 tablets every 4-6 hours as needed. Do not exceed 12 tablets in 24 hours.',
                'side_effects' => 'Stomach upset, heartburn, nausea, vomiting',
                'warnings' => 'Do not use if allergic to aspirin. Consult doctor if pregnant or breastfeeding.',
                'price' => 5.99,
                'requires_prescription' => false,
            ],
            [
                'name' => 'Ibuprofen',
                'generic_name' => 'Ibuprofen',
                'description' => 'Nonsteroidal anti-inflammatory drug (NSAID)',
                'dosage_form' => 'Tablet',
                'strength' => '200mg',
                'usage_instructions' => 'Take 1-2 tablets every 4-6 hours as needed with food or milk.',
                'side_effects' => 'Upset stomach, mild heartburn, dizziness, headache',
                'warnings' => 'May increase risk of heart attack or stroke. Do not use before or after heart surgery.',
                'price' => 7.99,
                'requires_prescription' => false,
            ],
            [
                'name' => 'Amoxicillin',
                'generic_name' => 'Amoxicillin',
                'description' => 'Antibiotic used to treat bacterial infections',
                'dosage_form' => 'Capsule',
                'strength' => '500mg',
                'usage_instructions' => 'Take as prescribed by your doctor, usually every 8-12 hours.',
                'side_effects' => 'Nausea, vomiting, diarrhea, rash',
                'warnings' => 'Complete the full course even if symptoms improve. May cause allergic reactions.',
                'price' => 15.99,
                'requires_prescription' => true,
            ],
            [
                'name' => 'Lisinopril',
                'generic_name' => 'Lisinopril',
                'description' => 'ACE inhibitor used to treat high blood pressure',
                'dosage_form' => 'Tablet',
                'strength' => '10mg',
                'usage_instructions' => 'Take once daily, with or without food.',
                'side_effects' => 'Dizziness, headache, persistent dry cough',
                'warnings' => 'Do not use during pregnancy. May cause kidney problems.',
                'price' => 12.99,
                'requires_prescription' => true,
            ],
            [
                'name' => 'Metformin',
                'generic_name' => 'Metformin Hydrochloride',
                'description' => 'Medication used to treat type 2 diabetes',
                'dosage_form' => 'Tablet',
                'strength' => '500mg',
                'usage_instructions' => 'Take with meals, usually 2-3 times daily.',
                'side_effects' => 'Nausea, vomiting, stomach upset, diarrhea, metallic taste',
                'warnings' => 'May cause lactic acidosis. Monitor kidney function regularly.',
                'price' => 10.99,
                'requires_prescription' => true,
            ],
        ];

        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            foreach ($medications as $medData) {
                Medication::create(array_merge($medData, ['tenant_id' => $tenant->id]));
            }
        }

        $this->command->info('Created '.count($medications).' medications for each tenant');
    }
}
