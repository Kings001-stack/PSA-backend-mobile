<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Storage;

echo "=== Testing Avatar Flow ===\n\n";

// Find a user
$user = User::where('email', 'ugwuemmanuelking@gmail.com')->first();

if (!$user) {
    echo "User not found!\n";
    exit(1);
}

echo "User: {$user->name} ({$user->email})\n";
echo "Current Avatar Path: " . ($user->avatar_path ?? 'NULL') . "\n";
echo "Current Avatar URL: " . ($user->avatar_url ?? 'NULL') . "\n";
echo "\n";

// Check storage configuration
echo "=== Storage Configuration ===\n";
echo "Storage Disk: " . config('filesystems.default') . "\n";
echo "Public Disk Root: " . Storage::disk('public')->path('') . "\n";
echo "APP_URL: " . config('app.url') . "\n";
echo "\n";

// Check if avatars directory exists
$avatarsPath = Storage::disk('public')->path('avatars');
echo "Avatars Directory: {$avatarsPath}\n";
echo "Avatars Directory Exists: " . (is_dir($avatarsPath) ? 'YES' : 'NO') . "\n";

if (!is_dir($avatarsPath)) {
    echo "Creating avatars directory...\n";
    Storage::disk('public')->makeDirectory('avatars');
    echo "Directory created!\n";
}

echo "\n";

// List existing avatars
echo "=== Existing Avatars ===\n";
$avatars = Storage::disk('public')->files('avatars');
if (empty($avatars)) {
    echo "No avatars found\n";
} else {
    foreach ($avatars as $avatar) {
        echo "- {$avatar}\n";
        echo "  Full Path: " . Storage::disk('public')->path($avatar) . "\n";
        echo "  URL: " . url('storage/' . $avatar) . "\n";
    }
}

echo "\n";

// Test URL generation
if ($user->avatar_path) {
    echo "=== Testing URL Generation ===\n";
    $testUrl = url('storage/' . $user->avatar_path);
    echo "Generated URL: {$testUrl}\n";
    echo "File Exists: " . (Storage::disk('public')->exists($user->avatar_path) ? 'YES' : 'NO') . "\n";
}

echo "\n=== Test Complete ===\n";
