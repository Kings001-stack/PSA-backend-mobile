<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$users = \App\Models\User::all(['id', 'name', 'email', 'role', 'account_status']);

echo "=== ALL USERS ===\n";
foreach ($users as $user) {
    echo sprintf(
        "ID: %d | Name: %s | Email: %s | Role: %s | Status: %s\n",
        $user->id,
        $user->name,
        $user->email,
        $user->role,
        $user->account_status
    );
}
echo "\n";
