<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== Testing Profile Avatar URL ===\n\n";

// Find a user
$user = User::where('email', 'ugwuemmanuelking@gmail.com')->first();

if (!$user) {
    echo "User not found!\n";
    exit(1);
}

echo "User ID: {$user->id}\n";
echo "User Name: {$user->name}\n";
echo "User Email: {$user->email}\n";
echo "Avatar Path: " . ($user->avatar_path ?? 'NULL') . "\n";
echo "Avatar URL (accessor): " . ($user->avatar_url ?? 'NULL') . "\n";
echo "\n";

// Test the accessor directly
$avatarUrl = $user->getAvatarUrlAttribute();
echo "Direct accessor call: " . ($avatarUrl ?? 'NULL') . "\n";

// Test toArray
$userArray = $user->toArray();
echo "\nUser Array Keys: " . implode(', ', array_keys($userArray)) . "\n";
echo "Avatar URL in array: " . ($userArray['avatar_url'] ?? 'NOT PRESENT') . "\n";

// Test JSON serialization
$userJson = $user->toJson();
echo "\nJSON Output:\n";
echo $userJson . "\n";

echo "\n=== Test Complete ===\n";
