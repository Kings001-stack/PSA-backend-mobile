<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== Complete System Integration Test ===\n\n";

$allPassed = true;

// Test 1: Role System
echo "TEST 1: Role System\n";
echo str_repeat("-", 60) . "\n";

$adminCount = User::where('role', 'admin')->count();
if ($adminCount === 0) {
    echo "✓ No users with deprecated 'admin' role\n";
} else {
    echo "✗ FAIL: Found {$adminCount} users with 'admin' role\n";
    $allPassed = false;
}

$validRoles = ['user', 'pharmacist', 'super_admin'];
$invalidUsers = User::whereNotIn('role', $validRoles)->whereNull('deleted_at')->count();
if ($invalidUsers === 0) {
    echo "✓ All users have valid roles\n";
} else {
    echo "✗ FAIL: Found {$invalidUsers} users with invalid roles\n";
    $allPassed = false;
}

echo "\n";

// Test 2: Test Accounts
echo "TEST 2: Test Accounts\n";
echo str_repeat("-", 60) . "\n";

$testAccounts = [
    ['email' => 'ugwuemmanuelking@gmail.com', 'role' => 'user', 'name' => 'User'],
    ['email' => 'pharmacist@1.test', 'role' => 'pharmacist', 'name' => 'Pharmacist'],
    ['email' => 'test@gmail.com', 'role' => 'pharmacist', 'name' => 'Pharmacist'],
    ['email' => 'superadmin@1.test', 'role' => 'super_admin', 'name' => 'Super Admin'],
];

foreach ($testAccounts as $account) {
    $user = User::where('email', $account['email'])->first();
    if ($user && $user->role === $account['role']) {
        echo "✓ {$account['name']}: {$account['email']} ({$account['role']})\n";
    } else {
        echo "✗ FAIL: {$account['name']}: {$account['email']} - ";
        if (!$user) {
            echo "NOT FOUND\n";
        } else {
            echo "Expected {$account['role']}, got {$user->role}\n";
        }
        $allPassed = false;
    }
}

echo "\n";

// Test 3: User Model Methods
echo "TEST 3: User Model Methods\n";
echo str_repeat("-", 60) . "\n";

$user = User::where('role', 'user')->first();
$pharmacist = User::where('role', 'pharmacist')->first();
$superAdmin = User::where('role', 'super_admin')->first();

$tests = [
    ['user' => $user, 'method' => 'isUser', 'expected' => true],
    ['user' => $user, 'method' => 'isPharmacist', 'expected' => false],
    ['user' => $user, 'method' => 'isAdmin', 'expected' => false],
    ['user' => $user, 'method' => 'isSuperAdmin', 'expected' => false],
    
    ['user' => $pharmacist, 'method' => 'isUser', 'expected' => false],
    ['user' => $pharmacist, 'method' => 'isPharmacist', 'expected' => true],
    ['user' => $pharmacist, 'method' => 'isAdmin', 'expected' => true],
    ['user' => $pharmacist, 'method' => 'isSuperAdmin', 'expected' => false],
    
    ['user' => $superAdmin, 'method' => 'isUser', 'expected' => false],
    ['user' => $superAdmin, 'method' => 'isPharmacist', 'expected' => false],
    ['user' => $superAdmin, 'method' => 'isAdmin', 'expected' => false],
    ['user' => $superAdmin, 'method' => 'isSuperAdmin', 'expected' => true],
];

foreach ($tests as $test) {
    if (!$test['user']) continue;
    
    $method = $test['method'];
    $result = $test['user']->$method();
    $expected = $test['expected'];
    
    if ($result === $expected) {
        echo "✓ {$test['user']->role}: {$method}() = " . ($result ? 'true' : 'false') . "\n";
    } else {
        echo "✗ FAIL: {$test['user']->role}: {$method}() expected " . ($expected ? 'true' : 'false') . ", got " . ($result ? 'true' : 'false') . "\n";
        $allPassed = false;
    }
}

echo "\n";

// Test 4: Role Hierarchy
echo "TEST 4: Role Hierarchy\n";
echo str_repeat("-", 60) . "\n";

if ($superAdmin && $pharmacist) {
    $canManage = $superAdmin->canManage($pharmacist);
    if ($canManage) {
        echo "✓ Super Admin can manage Pharmacist\n";
    } else {
        echo "✗ FAIL: Super Admin cannot manage Pharmacist\n";
        $allPassed = false;
    }
}

if ($pharmacist && $user) {
    $canManage = $pharmacist->canManage($user);
    if ($canManage) {
        echo "✓ Pharmacist can manage User\n";
    } else {
        echo "✗ FAIL: Pharmacist cannot manage User\n";
        $allPassed = false;
    }
}

if ($user && $pharmacist) {
    $canManage = $user->canManage($pharmacist);
    if (!$canManage) {
        echo "✓ User cannot manage Pharmacist\n";
    } else {
        echo "✗ FAIL: User can manage Pharmacist (should not be able to)\n";
        $allPassed = false;
    }
}

echo "\n";

// Test 5: Role Levels
echo "TEST 5: Role Levels\n";
echo str_repeat("-", 60) . "\n";

$roleLevels = [
    ['role' => 'user', 'expected' => 1],
    ['role' => 'pharmacist', 'expected' => 2],
    ['role' => 'super_admin', 'expected' => 3],
];

foreach ($roleLevels as $test) {
    $user = User::where('role', $test['role'])->first();
    if ($user) {
        $level = $user->getRoleLevel();
        if ($level === $test['expected']) {
            echo "✓ {$test['role']}: Level {$level}\n";
        } else {
            echo "✗ FAIL: {$test['role']}: Expected level {$test['expected']}, got {$level}\n";
            $allPassed = false;
        }
    }
}

echo "\n";

// Test 6: Avatar URLs
echo "TEST 6: Avatar System\n";
echo str_repeat("-", 60) . "\n";

$usersWithAvatars = User::whereNotNull('avatar_path')->count();
echo "Users with avatars: {$usersWithAvatars}\n";

$testUser = User::where('email', 'ugwuemmanuelking@gmail.com')->first();
if ($testUser) {
    $avatarUrl = $testUser->avatar_url;
    if ($testUser->avatar_path && $avatarUrl) {
        echo "✓ Avatar URL accessor working: {$avatarUrl}\n";
    } elseif (!$testUser->avatar_path) {
        echo "✓ No avatar set for test user (expected)\n";
    } else {
        echo "✗ FAIL: Avatar path exists but URL is null\n";
        $allPassed = false;
    }
}

echo "\n";

// Final Summary
echo "=== Test Summary ===\n";
echo str_repeat("=", 60) . "\n";

if ($allPassed) {
    echo "✓✓✓ ALL TESTS PASSED ✓✓✓\n";
    echo "\nThe system is ready for use with 3 roles:\n";
    echo "1. User - Regular users\n";
    echo "2. Pharmacist - Admin users (pharmacy management)\n";
    echo "3. Super Admin - System administrators\n";
} else {
    echo "✗✗✗ SOME TESTS FAILED ✗✗✗\n";
    echo "\nPlease review the failed tests above and fix the issues.\n";
}

echo "\nTotal Users: " . User::whereNull('deleted_at')->count() . "\n";
echo "- Users: " . User::where('role', 'user')->count() . "\n";
echo "- Pharmacists: " . User::where('role', 'pharmacist')->count() . "\n";
echo "- Super Admins: " . User::where('role', 'super_admin')->count() . "\n";

exit($allPassed ? 0 : 1);
