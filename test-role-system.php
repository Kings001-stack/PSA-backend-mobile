<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== Testing Role System (3 Roles Only) ===\n\n";

// Test 1: Check existing users and their roles
echo "TEST 1: Existing Users and Roles\n";
echo str_repeat("-", 50) . "\n";

$users = User::whereNull('deleted_at')->get();
$roleCounts = [
    'user' => 0,
    'pharmacist' => 0,
    'super_admin' => 0,
    'admin' => 0, // Should be 0 after migration
];

foreach ($users as $user) {
    if (isset($roleCounts[$user->role])) {
        $roleCounts[$user->role]++;
    }
    
    // Show first 5 users of each role
    if ($roleCounts[$user->role] <= 5) {
        echo sprintf(
            "%-30s %-20s %-15s\n",
            $user->name,
            $user->email,
            strtoupper($user->role)
        );
    }
}

echo "\nRole Distribution:\n";
echo "- Users: {$roleCounts['user']}\n";
echo "- Pharmacists: {$roleCounts['pharmacist']}\n";
echo "- Super Admins: {$roleCounts['super_admin']}\n";
echo "- Old Admins (should be 0): {$roleCounts['admin']}\n";

if ($roleCounts['admin'] > 0) {
    echo "\n⚠️  WARNING: Found {$roleCounts['admin']} users with 'admin' role!\n";
    echo "These should be migrated to 'pharmacist' role.\n";
}

echo "\n";

// Test 2: Test User Model Methods
echo "TEST 2: User Model Role Methods\n";
echo str_repeat("-", 50) . "\n";

$testUser = User::where('role', 'user')->first();
$testPharmacist = User::where('role', 'pharmacist')->first();
$testSuperAdmin = User::where('role', 'super_admin')->first();

if ($testUser) {
    echo "User ({$testUser->email}):\n";
    echo "  - isUser(): " . ($testUser->isUser() ? 'YES' : 'NO') . "\n";
    echo "  - isPharmacist(): " . ($testUser->isPharmacist() ? 'YES' : 'NO') . "\n";
    echo "  - isAdmin(): " . ($testUser->isAdmin() ? 'YES' : 'NO') . "\n";
    echo "  - isSuperAdmin(): " . ($testUser->isSuperAdmin() ? 'YES' : 'NO') . "\n";
    echo "  - getRoleLevel(): " . $testUser->getRoleLevel() . "\n\n";
}

if ($testPharmacist) {
    echo "Pharmacist ({$testPharmacist->email}):\n";
    echo "  - isUser(): " . ($testPharmacist->isUser() ? 'YES' : 'NO') . "\n";
    echo "  - isPharmacist(): " . ($testPharmacist->isPharmacist() ? 'YES' : 'NO') . "\n";
    echo "  - isAdmin(): " . ($testPharmacist->isAdmin() ? 'YES' : 'NO') . "\n";
    echo "  - isSuperAdmin(): " . ($testPharmacist->isSuperAdmin() ? 'YES' : 'NO') . "\n";
    echo "  - getRoleLevel(): " . $testPharmacist->getRoleLevel() . "\n\n";
}

if ($testSuperAdmin) {
    echo "Super Admin ({$testSuperAdmin->email}):\n";
    echo "  - isUser(): " . ($testSuperAdmin->isUser() ? 'YES' : 'NO') . "\n";
    echo "  - isPharmacist(): " . ($testSuperAdmin->isPharmacist() ? 'YES' : 'NO') . "\n";
    echo "  - isAdmin(): " . ($testSuperAdmin->isAdmin() ? 'YES' : 'NO') . "\n";
    echo "  - isSuperAdmin(): " . ($testSuperAdmin->isSuperAdmin() ? 'YES' : 'NO') . "\n";
    echo "  - getRoleLevel(): " . $testSuperAdmin->getRoleLevel() . "\n\n";
}

// Test 3: Test Role Hierarchy
echo "TEST 3: Role Hierarchy\n";
echo str_repeat("-", 50) . "\n";

if ($testSuperAdmin && $testPharmacist) {
    echo "Can Super Admin manage Pharmacist? ";
    echo $testSuperAdmin->canManage($testPharmacist) ? "YES ✓\n" : "NO ✗\n";
}

if ($testPharmacist && $testUser) {
    echo "Can Pharmacist manage User? ";
    echo $testPharmacist->canManage($testUser) ? "YES ✓\n" : "NO ✗\n";
}

if ($testUser && $testPharmacist) {
    echo "Can User manage Pharmacist? ";
    echo $testUser->canManage($testPharmacist) ? "NO ✓\n" : "YES ✗\n";
}

echo "\n";

// Test 4: Check for any 'admin' role in database
echo "TEST 4: Database Integrity Check\n";
echo str_repeat("-", 50) . "\n";

$adminRoleUsers = User::where('role', 'admin')->count();
echo "Users with 'admin' role: {$adminRoleUsers}\n";

if ($adminRoleUsers > 0) {
    echo "⚠️  ACTION REQUIRED: Migrate these users to 'pharmacist' role\n";
    echo "\nTo fix, run:\n";
    echo "UPDATE users SET role = 'pharmacist' WHERE role = 'admin';\n";
} else {
    echo "✓ No users with old 'admin' role found\n";
}

echo "\n";

// Test 5: Sample Test Accounts
echo "TEST 5: Test Accounts\n";
echo str_repeat("-", 50) . "\n";

$testAccounts = [
    'ugwuemmanuelking@gmail.com' => 'user',
    'pharmacist@1.test' => 'pharmacist',
    'test@gmail.com' => 'pharmacist',
    'superadmin@1.test' => 'super_admin',
];

foreach ($testAccounts as $email => $expectedRole) {
    $user = User::where('email', $email)->first();
    if ($user) {
        $match = $user->role === $expectedRole ? '✓' : '✗';
        echo sprintf(
            "%s %-35s Expected: %-12s Actual: %-12s\n",
            $match,
            $email,
            $expectedRole,
            $user->role
        );
    } else {
        echo "✗ {$email} - NOT FOUND\n";
    }
}

echo "\n=== Test Complete ===\n";
echo "\nSummary:\n";
echo "- Total Users: " . User::whereNull('deleted_at')->count() . "\n";
echo "- Valid Roles: user, pharmacist, super_admin\n";
echo "- Deprecated Roles: admin (should be 0)\n";

if ($roleCounts['admin'] > 0) {
    echo "\n⚠️  MIGRATION NEEDED: {$roleCounts['admin']} users still have 'admin' role\n";
} else {
    echo "\n✓ All users have valid roles\n";
}
