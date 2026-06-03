<?php

/**
 * Add Test Patients to PrimeChem Pharmacy System
 * 
 * Purpose: Add 5 realistic test patients with phone numbers for testing
 * Usage: php add_test_patients.php
 * 
 * This script will:
 * 1. Add 5 test patients to Tenant 1
 * 2. Create sample refill requests for some patients
 * 3. Display a summary of what was created
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\RefillRequest;
use App\Models\Medication;

echo "\n";
echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║  PrimeChem Pharmacy - Add Test Patients                 ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n";
echo "\n";

try {
    // Check if patients already exist
    $existingPatients = User::whereIn('email', [
        'john.smith@example.com',
        'sarah.j@example.com',
        'michael.b@example.com',
        'emily.davis@example.com',
        'james.w@example.com'
    ])->count();

    if ($existingPatients > 0) {
        echo "⚠️  Warning: Some test patients already exist in the database.\n";
        echo "   Found: {$existingPatients} existing patient(s)\n";
        echo "\n";
        echo "   Do you want to continue and update them? (y/n): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        if (trim($line) !== 'y' && trim($line) !== 'Y') {
            echo "\n";
            echo "❌ Cancelled. No changes made.\n";
            echo "\n";
            exit(0);
        }
        fclose($handle);
    }

    echo "📊 Starting patient creation...\n";
    echo "\n";

    DB::beginTransaction();

    // Test Patients Data
    $patients = [
        [
            'name' => 'John Smith',
            'email' => 'john.smith@example.com',
            'phone' => '555-0101',
            'refills' => 2
        ],
        [
            'name' => 'Sarah Johnson',
            'email' => 'sarah.j@example.com',
            'phone' => '555-0102',
            'refills' => 1
        ],
        [
            'name' => 'Michael Brown',
            'email' => 'michael.b@example.com',
            'phone' => '555-0103',
            'refills' => 1
        ],
        [
            'name' => 'Emily Davis',
            'email' => 'emily.davis@example.com',
            'phone' => '555-0104',
            'refills' => 1
        ],
        [
            'name' => 'James Wilson',
            'email' => 'james.w@example.com',
            'phone' => '555-0105',
            'refills' => 0
        ]
    ];

    $createdUsers = [];
    $updatedUsers = [];

    foreach ($patients as $patientData) {
        $user = User::updateOrCreate(
            ['email' => $patientData['email']],
            [
                'name' => $patientData['name'],
                'password' => Hash::make('password'),
                'role' => 'user',
                'tenant_id' => 1,
                'phone' => $patientData['phone'],
                'account_status' => 'active',
            ]
        );

        if ($user->wasRecentlyCreated) {
            $createdUsers[] = $user;
            echo "✅ Created: {$user->name} ({$user->email})\n";
        } else {
            $updatedUsers[] = $user;
            echo "🔄 Updated: {$user->name} ({$user->email})\n";
        }
    }

    echo "\n";
    echo "📋 Adding sample refill requests...\n";
    echo "\n";

    // Get first medication for testing (or create a dummy one)
    $medication = Medication::where('tenant_id', 1)->first();
    
    if (!$medication) {
        echo "⚠️  No medications found in database. Skipping refill requests.\n";
        echo "   Please add medications through the Inventory Management system.\n";
    } else {
        $refillsCreated = 0;

        // John Smith - 2 refills
        $johnSmith = User::where('email', 'john.smith@example.com')->first();
        if ($johnSmith) {
            // Pending refill
            RefillRequest::create([
                'tenant_id' => 1,
                'user_id' => $johnSmith->id,
                'medication_id' => $medication->id,
                'quantity' => 30,
                'status' => 'pending',
                'notes' => 'Please refill my medication as soon as possible',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $refillsCreated++;

            // Approved refill (5 days ago)
            RefillRequest::create([
                'tenant_id' => 1,
                'user_id' => $johnSmith->id,
                'medication_id' => $medication->id,
                'quantity' => 60,
                'status' => 'approved',
                'notes' => 'Regular monthly refill',
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ]);
            $refillsCreated++;
        }

        // Sarah Johnson - 1 pending refill
        $sarahJohnson = User::where('email', 'sarah.j@example.com')->first();
        if ($sarahJohnson) {
            RefillRequest::create([
                'tenant_id' => 1,
                'user_id' => $sarahJohnson->id,
                'medication_id' => $medication->id,
                'quantity' => 30,
                'status' => 'pending',
                'notes' => 'Running low on medication',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $refillsCreated++;
        }

        // Michael Brown - 1 ready for pickup
        $michaelBrown = User::where('email', 'michael.b@example.com')->first();
        if ($michaelBrown) {
            RefillRequest::create([
                'tenant_id' => 1,
                'user_id' => $michaelBrown->id,
                'medication_id' => $medication->id,
                'quantity' => 90,
                'status' => 'ready_for_pickup',
                'notes' => 'Need this urgently',
                'is_urgent' => true,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ]);
            $refillsCreated++;
        }

        // Emily Davis - 1 collected
        $emilyDavis = User::where('email', 'emily.davis@example.com')->first();
        if ($emilyDavis) {
            RefillRequest::create([
                'tenant_id' => 1,
                'user_id' => $emilyDavis->id,
                'medication_id' => $medication->id,
                'quantity' => 30,
                'status' => 'collected',
                'notes' => 'Thank you for the service',
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(10),
            ]);
            $refillsCreated++;
        }

        echo "✅ Created {$refillsCreated} sample refill requests\n";
    }

    DB::commit();

    echo "\n";
    echo "╔══════════════════════════════════════════════════════════╗\n";
    echo "║  ✅ SUCCESS - Test Patients Added                       ║\n";
    echo "╚══════════════════════════════════════════════════════════╝\n";
    echo "\n";

    echo "📊 Summary:\n";
    echo "   • Created: " . count($createdUsers) . " new patient(s)\n";
    echo "   • Updated: " . count($updatedUsers) . " existing patient(s)\n";
    echo "   • Tenant: 1 (Primary test tenant)\n";
    echo "\n";

    echo "👥 Test Patients:\n";
    echo "\n";
    
    $allTestPatients = User::where('tenant_id', 1)
        ->where('role', 'user')
        ->orderBy('id')
        ->get(['id', 'name', 'email', 'phone', 'account_status']);

    foreach ($allTestPatients as $patient) {
        $refillCount = RefillRequest::where('user_id', $patient->id)->count();
        echo sprintf(
            "   %-3s %-20s %-30s %-15s %d refills\n",
            "#{$patient->id}",
            $patient->name,
            $patient->email,
            $patient->phone ?: 'No phone',
            $refillCount
        );
    }

    echo "\n";
    echo "🔑 Login Credentials:\n";
    echo "   All patients have password: password\n";
    echo "\n";

    echo "🧪 Test as Pharmacist:\n";
    echo "   Email: pharmacist@1.test\n";
    echo "   Password: password\n";
    echo "   Expected: See " . $allTestPatients->count() . " patients\n";
    echo "\n";

    echo "🔐 Test as Super Admin:\n";
    echo "   Email: admin@1.test\n";
    echo "   Password: password\n";
    echo "   Expected: See ALL users (patients + pharmacists)\n";
    echo "\n";

    echo "🚀 Next Steps:\n";
    echo "   1. Hard refresh your browser (Ctrl+Shift+R)\n";
    echo "   2. Login as pharmacist@1.test\n";
    echo "   3. Navigate to Users tab\n";
    echo "   4. You should see {$allTestPatients->count()} patients\n";
    echo "\n";

} catch (\Exception $e) {
    DB::rollBack();
    
    echo "\n";
    echo "❌ ERROR: Failed to create test patients\n";
    echo "\n";
    echo "Error Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\n";
    
    exit(1);
}
