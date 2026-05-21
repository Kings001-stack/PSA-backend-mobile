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
        // Get the first tenant (or create one if none exists)
        $tenant = Tenant::first();
        
        if (!$tenant) {
            $tenant = Tenant::create([
                'name' => 'PrimeChem Pharmacy',
                'domain' => 'primechem',
                'database' => 'primechem_db',
            ]);
        }

        $medications = [
            // Pain Relief
            [
                'name' => 'Paracetamol 500mg',
                'generic_name' => 'Acetaminophen',
                'description' => 'Pain reliever and fever reducer',
                'dosage_form' => 'Tablet',
                'strength' => '500mg',
                'usage_instructions' => 'Take 1-2 tablets every 4-6 hours as needed. Do not exceed 8 tablets in 24 hours.',
                'side_effects' => 'Rare: nausea, rash, liver damage with overdose',
                'warnings' => 'Do not use with other products containing acetaminophen. Avoid alcohol.',
                'price' => 5.99,
                'requires_prescription' => false,
            ],
            [
                'name' => 'Ibuprofen 400mg',
                'generic_name' => 'Ibuprofen',
                'description' => 'Anti-inflammatory pain reliever',
                'dosage_form' => 'Tablet',
                'strength' => '400mg',
                'usage_instructions' => 'Take 1 tablet every 6-8 hours with food. Maximum 3 tablets daily.',
                'side_effects' => 'Stomach upset, heartburn, dizziness',
                'warnings' => 'Take with food. Avoid if you have stomach ulcers or kidney problems.',
                'price' => 8.99,
                'requires_prescription' => false,
            ],
            
            // Antibiotics (Prescription Required)
            [
                'name' => 'Amoxicillin 500mg',
                'generic_name' => 'Amoxicillin',
                'description' => 'Antibiotic for bacterial infections',
                'dosage_form' => 'Capsule',
                'strength' => '500mg',
                'usage_instructions' => 'Take 1 capsule three times daily for 7-10 days. Complete full course.',
                'side_effects' => 'Diarrhea, nausea, rash',
                'warnings' => 'Complete full course even if symptoms improve. Report any allergic reactions immediately.',
                'price' => 15.99,
                'requires_prescription' => true,
            ],
            [
                'name' => 'Azithromycin 250mg',
                'generic_name' => 'Azithromycin',
                'description' => 'Macrolide antibiotic',
                'dosage_form' => 'Tablet',
                'strength' => '250mg',
                'usage_instructions' => 'Day 1: 2 tablets. Days 2-5: 1 tablet daily.',
                'side_effects' => 'Nausea, diarrhea, stomach pain',
                'warnings' => 'Take on empty stomach. Complete full course.',
                'price' => 22.99,
                'requires_prescription' => true,
            ],
            
            // Blood Pressure (Prescription Required)
            [
                'name' => 'Amlodipine 5mg',
                'generic_name' => 'Amlodipine',
                'description' => 'Calcium channel blocker for high blood pressure',
                'dosage_form' => 'Tablet',
                'strength' => '5mg',
                'usage_instructions' => 'Take 1 tablet once daily, same time each day.',
                'side_effects' => 'Swelling of ankles, dizziness, flushing',
                'warnings' => 'Do not stop suddenly. Monitor blood pressure regularly.',
                'price' => 12.99,
                'requires_prescription' => true,
            ],
            [
                'name' => 'Lisinopril 10mg',
                'generic_name' => 'Lisinopril',
                'description' => 'ACE inhibitor for high blood pressure',
                'dosage_form' => 'Tablet',
                'strength' => '10mg',
                'usage_instructions' => 'Take 1 tablet once daily.',
                'side_effects' => 'Dry cough, dizziness, headache',
                'warnings' => 'Avoid potassium supplements. Report persistent cough.',
                'price' => 10.99,
                'requires_prescription' => true,
            ],
            
            // Diabetes (Prescription Required)
            [
                'name' => 'Metformin 500mg',
                'generic_name' => 'Metformin',
                'description' => 'Oral diabetes medication',
                'dosage_form' => 'Tablet',
                'strength' => '500mg',
                'usage_instructions' => 'Take 1 tablet twice daily with meals.',
                'side_effects' => 'Nausea, diarrhea, stomach upset',
                'warnings' => 'Take with food. Monitor blood sugar regularly.',
                'price' => 14.99,
                'requires_prescription' => true,
            ],
            
            // Allergy Relief
            [
                'name' => 'Cetirizine 10mg',
                'generic_name' => 'Cetirizine',
                'description' => 'Antihistamine for allergies',
                'dosage_form' => 'Tablet',
                'strength' => '10mg',
                'usage_instructions' => 'Take 1 tablet once daily.',
                'side_effects' => 'Drowsiness, dry mouth, fatigue',
                'warnings' => 'May cause drowsiness. Avoid alcohol.',
                'price' => 7.99,
                'requires_prescription' => false,
            ],
            [
                'name' => 'Loratadine 10mg',
                'generic_name' => 'Loratadine',
                'description' => 'Non-drowsy antihistamine',
                'dosage_form' => 'Tablet',
                'strength' => '10mg',
                'usage_instructions' => 'Take 1 tablet once daily.',
                'side_effects' => 'Headache, dry mouth',
                'warnings' => 'Non-drowsy formula. Safe for daytime use.',
                'price' => 9.99,
                'requires_prescription' => false,
            ],
            
            // Stomach/Digestive
            [
                'name' => 'Omeprazole 20mg',
                'generic_name' => 'Omeprazole',
                'description' => 'Proton pump inhibitor for acid reflux',
                'dosage_form' => 'Capsule',
                'strength' => '20mg',
                'usage_instructions' => 'Take 1 capsule once daily before breakfast.',
                'side_effects' => 'Headache, nausea, diarrhea',
                'warnings' => 'Take before meals. May take 1-4 days for full effect.',
                'price' => 11.99,
                'requires_prescription' => false,
            ],
            
            // Vitamins & Supplements
            [
                'name' => 'Vitamin D3 1000IU',
                'generic_name' => 'Cholecalciferol',
                'description' => 'Vitamin D supplement',
                'dosage_form' => 'Tablet',
                'strength' => '1000IU',
                'usage_instructions' => 'Take 1 tablet daily with food.',
                'side_effects' => 'Rare: nausea, constipation',
                'warnings' => 'Take with food for better absorption.',
                'price' => 6.99,
                'requires_prescription' => false,
            ],
            [
                'name' => 'Multivitamin Daily',
                'generic_name' => 'Multivitamin Complex',
                'description' => 'Complete daily vitamin supplement',
                'dosage_form' => 'Tablet',
                'strength' => 'Standard',
                'usage_instructions' => 'Take 1 tablet daily with food.',
                'side_effects' => 'Rare: upset stomach',
                'warnings' => 'Take with food. Do not exceed recommended dose.',
                'price' => 12.99,
                'requires_prescription' => false,
            ],
        ];

        foreach ($medications as $medication) {
            Medication::create(array_merge($medication, [
                'tenant_id' => $tenant->id,
            ]));
        }

        $this->command->info('✓ Seeded ' . count($medications) . ' medications');
    }
}
