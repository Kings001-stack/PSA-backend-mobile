<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Use a pre-computed hash for "password" with bcrypt rounds=10
// This hash was generated with: password_hash('password', PASSWORD_BCRYPT, ['cost' => 10])
$passwordHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

DB::table('users')
    ->where('email', 'admin@1.test')
    ->update(['password' => $passwordHash]);

echo "Password updated successfully for admin@1.test\n";
echo "You can now login with: admin@1.test / password\n";
