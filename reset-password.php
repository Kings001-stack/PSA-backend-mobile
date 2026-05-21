<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$email = $argv[1] ?? 'admin@1.test';
$password = $argv[2] ?? 'password';

$user = \App\Models\User::where('email', $email)->first();

if ($user) {
    $user->password = \Illuminate\Support\Facades\Hash::make($password);
    $user->save();
    
    echo "Password reset successfully for: {$user->email}\n";
    echo "New password: {$password}\n";
} else {
    echo "User not found: $email\n";
}
