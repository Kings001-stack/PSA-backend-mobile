<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Inventory;
use App\Models\Medication;
use App\Http\Controllers\Api\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

echo "--- SYSTEM VERIFICATION START ---\n";

// 1. DATA SETUP
echo "\n[1] Finding Test User...\n";
$user = User::where('email', 'admin@example.com')->first();
if (!$user) {
    echo "Creating admin@example.com...\n";
    $user = User::create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
        'role' => 'admin',
        'tenant_id' => 1
    ]);
}
echo "User ID: {$user->id} (Role: {$user->role})\n";

// 2. ADMIN DASHBOARD TEST
echo "\n[2] Testing Admin Dashboard (AdminController::dashboard)...\n";
try {
    $controller = app(AdminController::class);
    $request = Request::create('/api/admin/dashboard', 'GET');
    $request->setUserResolver(function () use ($user) {
        return $user;
    });

    $response = $controller->dashboard($request);
    $data = $response->getData(true);

    if (isset($data['stats'])) {
        echo "[PASS] Dashboard Stats Fetched:\n";
        echo "  - Total Medications: " . $data['stats']['total_medications'] . "\n";
        echo "  - Low Stock Items: " . $data['stats']['low_stock_count'] . "\n";
        echo "  - Total Value: $" . $data['stats']['total_value'] . "\n";
    } else {
        echo "[FAIL] Unexpected Dashboard Response Format\n";
        print_r($data);
    }
} catch (\Exception $e) {
    echo "[FAIL] Dashboard Error: " . $e->getMessage() . "\n";
}

// 3. INVENTORY TEST
echo "\n[3] Checking Inventory Data...\n";
$medCount = Medication::count();
$invCount = Inventory::count();
echo "  - Medications in DB: $medCount\n";
echo "  - Inventory Records: $invCount\n";

if ($invCount == 0) {
    echo "  ! Inventory is empty. Frontend will show valid empty state.\n";
}

// 4. AUTHENTICATION TEST (Login)
echo "\n[4] Testing Login (AuthController::login)...\n";
try {
    $authController = app(\App\Http\Controllers\Api\AuthController::class);
    $loginRequest = Request::create('/api/login', 'POST', [
        'email' => 'admin@example.com',
        'password' => 'password',
        'device_name' => 'verification_script'
    ]);
    
    $loginResponse = $authController->login($loginRequest);
    $loginData = $loginResponse->getData(true);
    
    if (isset($loginData['access_token'])) {
        echo "[PASS] Login Successful. Token generated.\n";
    } else {
        echo "[FAIL] Login Failed.\n";
        print_r($loginData);
    }
} catch (\Exception $e) {
    echo "[FAIL] Login Error: " . $e->getMessage() . "\n";
}

// 5. AI FUNCTIONALITY TEST (Real Generation)
echo "\n[5] Testing AI Generation (Actual API Call)...\n";
echo "  - Provider: " . config('ai.provider', 'NOT SET') . "\n";

try {
    $ai = app(\App\Contracts\AIProviderInterface::class);
    echo "  - Sending prompt: 'Say hello in 5 words or less'...\n";
    
    $start = microtime(true);
    $response = $ai->generateResponse("Say hello in 5 words or less");
    $duration = round(microtime(true) - $start, 2);
    
    echo "  - Response ({$duration}s): \"$response\"\n";
    
    if (!empty($response)) {
        echo "[PASS] AI is functional.\n";
    } else {
        echo "[FAIL] AI returned empty response.\n";
    }
} catch (\Exception $e) {
    echo "[FAIL] AI Error: " . $e->getMessage() . "\n";
}

echo "\n--- VERIFICATION COMPLETE ---\n";
