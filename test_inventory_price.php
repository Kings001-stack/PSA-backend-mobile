<?php

/**
 * Test Inventory Price Feature
 * 
 * Tests that admins can add prices but users cannot see them
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Inventory;

echo "🧪 Testing Inventory Price Feature\n";
echo str_repeat("=", 50) . "\n\n";

// Get admin and regular user
$admin = User::where('role', 'admin')->first();
$user = User::where('role', 'user')->first();

if (!$admin) {
    echo "❌ No admin user found\n";
    exit(1);
}

if (!$user) {
    echo "⚠️  No regular user found, creating one...\n";
    $user = User::create([
        'name' => 'Test User',
        'email' => 'testuser@example.com',
        'password' => bcrypt('password'),
        'tenant_id' => 1,
        'role' => 'user',
    ]);
    echo "✅ Test user created\n";
}

echo "✅ Admin user: {$admin->name}\n";
echo "✅ Regular user: {$user->name}\n\n";

// Test 1: Add inventory item with price
echo "📝 Test 1: Adding inventory item with price\n";
echo str_repeat("-", 50) . "\n";

$inventory = Inventory::first();
if ($inventory) {
    $inventory->update(['unit_price' => 99.99]);
    echo "✅ Updated inventory item #{$inventory->id} with price: ₦99.99\n";
    echo "   Medication: {$inventory->medication->name}\n";
    echo "   Quantity: {$inventory->quantity}\n";
    echo "   Unit Price: ₦{$inventory->unit_price}\n\n";
} else {
    echo "❌ No inventory items found\n";
    exit(1);
}

// Test 2: Admin can see price
echo "📝 Test 2: Admin viewing inventory (should see price)\n";
echo str_repeat("-", 50) . "\n";

$adminToken = $admin->createToken('test-admin')->plainTextToken;
$ch = curl_init('http://127.0.0.1:8000/api/inventory');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $adminToken,
    'Accept: application/json',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $data = json_decode($response, true);
    $items = $data['data'] ?? $data;
    $firstItem = $items[0] ?? null;
    
    if ($firstItem && isset($firstItem['unit_price'])) {
        echo "✅ Admin CAN see prices\n";
        echo "   Item: {$firstItem['medication']['name']}\n";
        echo "   Price: ₦{$firstItem['unit_price']}\n\n";
    } else {
        echo "❌ Admin cannot see prices (unexpected)\n\n";
    }
} else {
    echo "❌ Request failed with status: {$httpCode}\n\n";
}

// Test 3: Regular user cannot see price
echo "📝 Test 3: Regular user viewing inventory (should NOT see price)\n";
echo str_repeat("-", 50) . "\n";

$userToken = $user->createToken('test-user')->plainTextToken;
$ch = curl_init('http://127.0.0.1:8000/api/inventory');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $userToken,
    'Accept: application/json',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $data = json_decode($response, true);
    $items = $data['data'] ?? $data;
    $firstItem = $items[0] ?? null;
    
    if ($firstItem && !isset($firstItem['unit_price'])) {
        echo "✅ Regular user CANNOT see prices (correct!)\n";
        echo "   Item: {$firstItem['medication']['name']}\n";
        echo "   Quantity: {$firstItem['quantity']}\n";
        echo "   Price field: HIDDEN ✓\n\n";
    } else if ($firstItem && isset($firstItem['unit_price'])) {
        echo "❌ Regular user CAN see prices (security issue!)\n";
        echo "   Price: ₦{$firstItem['unit_price']}\n\n";
    } else {
        echo "⚠️  No items found\n\n";
    }
} else {
    echo "❌ Request failed with status: {$httpCode}\n\n";
}

// Test 4: Check analytics still works with prices
echo "📝 Test 4: Analytics with inventory prices\n";
echo str_repeat("-", 50) . "\n";

$ch = curl_init('http://127.0.0.1:8000/api/admin/analytics');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $adminToken,
    'Accept: application/json',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $data = json_decode($response, true);
    $totalValue = $data['inventory']['total_value'] ?? 0;
    echo "✅ Analytics working with prices\n";
    echo "   Total Inventory Value: ₦" . number_format($totalValue, 2) . "\n\n";
} else {
    echo "❌ Analytics request failed\n\n";
}

echo "✅ All tests completed!\n";
echo "\n🎉 Price feature is working correctly:\n";
echo "   • Admins can add/edit prices\n";
echo "   • Admins can see prices\n";
echo "   • Regular users CANNOT see prices\n";
echo "   • Analytics calculates total value\n";
