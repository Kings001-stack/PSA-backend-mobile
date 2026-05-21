<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$email = $argv[1] ?? 'user@gmail.com';
$user = \App\Models\User::where('email', $email)->first();

if ($user) {
    echo sprintf(
        "Found: %s | %s | %s | %s\n",
        $user->name,
        $user->email,
        $user->role,
        $user->account_status
    );
} else {
    echo "User not found: $email\n";
}
