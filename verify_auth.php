<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

echo "--- Testing Token Authentication ---\n";

// 1. Create/Find a test admin user
$email = 'debug_admin@test.com';
$user = User::where('email', $email)->first();

if (!$user) {
    $user = User::create([
        'name' => 'Debug Admin',
        'email' => $email,
        'password' => Hash::make('password123'),
        'role' => 'admin',
        'tenant_id' => 1
    ]);
    echo "Created new debug user.\n";
} else {
    echo "Using existing debug user.\n";
}

// 2. Create a token
$tokenResult = $user->createToken('test_token');
$plainToken = $tokenResult->plainTextToken;
echo "Generated Token: " . $plainToken . "\n";

// 3. Try to hit the dashboard endpoint internally using the token
// We simulate the middleware by manually setting the Authorization header
$request = Illuminate\Http\Request::create('/api/admin/dashboard', 'GET');
$request->headers->set('Authorization', 'Bearer ' . $plainToken);
$request->headers->set('Accept', 'application/json');

// We need to handle the request through the kernel to trigger middleware
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request);

echo "Status Code: " . $response->getStatusCode() . "\n";
echo "Response Body: " . $response->getContent() . "\n";

if ($response->getStatusCode() == 401) {
    echo "FAILURE: Token was rejected with 401.\n";
} else if ($response->getStatusCode() == 403) {
    echo "FAILURE: Token was accepted but unauthorized (403).\n";
} else {
    echo "SUCCESS: Dashboard accessible with token.\n";
}
