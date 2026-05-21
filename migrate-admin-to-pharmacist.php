<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== Migrating 'admin' role to 'pharmacist' ===\n\n";

// Find all users with 'admin' role
$adminUsers = User::where('role', 'admin')->get();

if ($adminUsers->isEmpty()) {
    echo "✓ No users with 'admin' role found. Migration not needed.\n";
    exit(0);
}

echo "Found {$adminUsers->count()} users with 'admin' role:\n";
echo str_repeat("-", 60) . "\n";

foreach ($adminUsers as $user) {
    echo sprintf(
        "%-30s %-30s\n",
        $user->name,
        $user->email
    );
}

echo str_repeat("-", 60) . "\n";
echo "\nMigrating to 'pharmacist' role...\n\n";

$updated = 0;
foreach ($adminUsers as $user) {
    $user->role = 'pharmacist';
    if ($user->save()) {
        echo "✓ {$user->email} -> pharmacist\n";
        $updated++;
    } else {
        echo "✗ Failed to update {$user->email}\n";
    }
}

echo "\n=== Migration Complete ===\n";
echo "Successfully updated {$updated} users\n";

// Verify
$remainingAdmins = User::where('role', 'admin')->count();
if ($remainingAdmins === 0) {
    echo "✓ All users migrated successfully\n";
} else {
    echo "⚠️  Warning: {$remainingAdmins} users still have 'admin' role\n";
}
