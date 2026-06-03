<?php

/**
 * Reset All User Passwords to "password"
 * 
 * Purpose: Ensure all user accounts have password set to "password" for easy testing
 * Usage: php reset_all_passwords.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

echo "\n";
echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║  PrimeChem Pharmacy - Reset All Passwords               ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n";
echo "\n";

try {
    $totalUsers = User::count();
    
    echo "📊 Found {$totalUsers} user(s) in the database\n";
    echo "\n";
    echo "🔐 Resetting all passwords to: password\n";
    echo "\n";

    DB::beginTransaction();

    // Generate password hash once (more efficient)
    $passwordHash = Hash::make('password');

    // Update all users
    $updated = User::query()->update([
        'password' => $passwordHash
    ]);

    DB::commit();

    echo "✅ Successfully updated {$updated} user password(s)\n";
    echo "\n";

    // Display all users with their emails
    echo "👥 All Users:\n";
    echo "\n";

    $allUsers = User::orderBy('tenant_id')->orderBy('role')->orderBy('id')->get(['id', 'name', 'email', 'role', 'tenant_id']);

    $currentTenant = null;
    foreach ($allUsers as $user) {
        if ($currentTenant !== $user->tenant_id) {
            $currentTenant = $user->tenant_id;
            echo "\n";
            echo "📍 Tenant {$user->tenant_id}:\n";
            echo str_repeat("─", 60) . "\n";
        }

        $roleEmoji = match($user->role) {
            'super_admin' => '👑',
            'pharmacist' => '💊',
            'user' => '👤',
            default => '❓'
        };

        $roleLabel = match($user->role) {
            'super_admin' => 'Super Admin',
            'pharmacist' => 'Pharmacist',
            'user' => 'Patient',
            default => $user->role
        };

        echo sprintf(
            "   %s %-3s %-25s %-35s %s\n",
            $roleEmoji,
            "#{$user->id}",
            $user->name,
            $user->email,
            $roleLabel
        );
    }

    echo "\n";
    echo "╔══════════════════════════════════════════════════════════╗\n";
    echo "║  ✅ SUCCESS - All Passwords Reset                       ║\n";
    echo "╚══════════════════════════════════════════════════════════╝\n";
    echo "\n";

    echo "🔑 Login Credentials for ALL users:\n";
    echo "   Password: password\n";
    echo "\n";

    echo "🧪 Test Accounts (Tenant 1):\n";
    echo "\n";
    echo "   Super Admin:\n";
    echo "   • Email: admin@1.test\n";
    echo "   • Password: password\n";
    echo "   • Access: Full system control\n";
    echo "\n";
    echo "   Pharmacist:\n";
    echo "   • Email: pharmacist@1.test\n";
    echo "   • Password: password\n";
    echo "   • Access: View patients, manage refills\n";
    echo "\n";
    echo "   Patients:\n";
    echo "   • staff@1.test / password\n";
    echo "   • john.smith@example.com / password\n";
    echo "   • sarah.j@example.com / password\n";
    echo "   • michael.b@example.com / password\n";
    echo "   • emily.davis@example.com / password\n";
    echo "   • james.w@example.com / password\n";
    echo "\n";

    echo "🧪 Test Accounts (Tenant 2):\n";
    echo "\n";
    echo "   Super Admin:\n";
    echo "   • Email: admin@2.test\n";
    echo "   • Password: password\n";
    echo "\n";
    echo "   Pharmacist:\n";
    echo "   • Email: pharmacist@2.test\n";
    echo "   • Password: password\n";
    echo "\n";
    echo "   Patient:\n";
    echo "   • staff@2.test / password\n";
    echo "\n";

    echo "🚀 Ready to Login:\n";
    echo "   Navigate to your application and login with any email above\n";
    echo "   All passwords are now: password\n";
    echo "\n";

} catch (\Exception $e) {
    DB::rollBack();
    
    echo "\n";
    echo "❌ ERROR: Failed to reset passwords\n";
    echo "\n";
    echo "Error Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\n";
    
    exit(1);
}
