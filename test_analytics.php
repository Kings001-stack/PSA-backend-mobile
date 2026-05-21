<?php

/**
 * Analytics API Test Script
 * 
 * Tests the analytics endpoint with sample data
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Medication;
use App\Models\Inventory;
use App\Models\RefillRequest;

echo "🧪 Testing Analytics API\n";
echo str_repeat("=", 50) . "\n\n";

// Get admin user
$admin = User::where('role', 'admin')->first();

if (!$admin) {
    echo "❌ No admin user found. Please create one first.\n";
    exit(1);
}

echo "✅ Admin user found: {$admin->name}\n";
echo "   Tenant ID: {$admin->tenant_id}\n\n";

// Create test token
$token = $admin->createToken('test-analytics')->plainTextToken;
echo "🔑 Test token created\n\n";

// Test the analytics endpoint
$baseUrl = 'http://127.0.0.1:8000';
$url = "{$baseUrl}/api/admin/analytics";

echo "📊 Fetching analytics from: {$url}\n\n";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json',
    'Content-Type: application/json',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo "❌ Request failed with status code: {$httpCode}\n";
    echo "Response: {$response}\n";
    exit(1);
}

$data = json_decode($response, true);

if (!$data) {
    echo "❌ Failed to parse JSON response\n";
    exit(1);
}

echo "✅ Analytics data retrieved successfully!\n\n";

// Display overview stats
echo "📈 OVERVIEW STATISTICS\n";
echo str_repeat("-", 50) . "\n";
echo "Total Medications:        {$data['overview']['total_medications']}\n";
echo "Total Inventory Items:    {$data['overview']['total_inventory_items']}\n";
echo "Low Stock Count:          {$data['overview']['low_stock_count']}\n";
echo "Total Conversations:      {$data['overview']['total_conversations']}\n";
echo "Active Conversations:     {$data['overview']['active_conversations']}\n";
echo "Escalated Conversations:  {$data['overview']['escalated_conversations']}\n";
echo "Total Users:              {$data['overview']['total_users']}\n";
echo "Pending Refills:          {$data['overview']['pending_refills']}\n";
echo "Total Messages:           {$data['overview']['total_messages']}\n\n";

// Display conversation stats
echo "💬 CONVERSATION STATISTICS\n";
echo str_repeat("-", 50) . "\n";
echo "Total:                    {$data['conversations']['total']}\n";
echo "Escalation Rate:          {$data['conversations']['escalation_rate']}%\n";
echo "Avg Messages/Conv:        {$data['conversations']['avg_messages_per_conversation']}\n\n";

// Display inventory stats
echo "📦 INVENTORY STATISTICS\n";
echo str_repeat("-", 50) . "\n";
echo "Total Items:              {$data['inventory']['total_items']}\n";
echo "Total Value:              ₦" . number_format($data['inventory']['total_value'], 2) . "\n";
echo "Low Stock Items:          {$data['inventory']['low_stock_items']}\n";
echo "Expiring Soon:            {$data['inventory']['expiring_soon']}\n\n";

// Display top medications
if (!empty($data['inventory']['top_medications'])) {
    echo "🏆 TOP MEDICATIONS BY STOCK\n";
    echo str_repeat("-", 50) . "\n";
    foreach (array_slice($data['inventory']['top_medications'], 0, 5) as $index => $med) {
        echo ($index + 1) . ". {$med['name']}: {$med['quantity']} units (₦" . number_format($med['value'], 2) . ")\n";
    }
    echo "\n";
}

// Display refill stats
echo "💊 REFILL REQUEST STATISTICS\n";
echo str_repeat("-", 50) . "\n";
echo "Total:                    {$data['refills']['total']}\n";
echo "Approval Rate:            {$data['refills']['approval_rate']}%\n\n";

// Display trend data
echo "📊 TREND DATA (Last 7 Days)\n";
echo str_repeat("-", 50) . "\n";
foreach ($data['trends']['last_7_days'] as $day) {
    echo "{$day['date']}: {$day['conversations']} conversations, {$day['messages']} messages, {$day['refills']} refills\n";
}
echo "\n";

echo "✅ Analytics test completed successfully!\n";
echo "\n🎉 All systems operational!\n";
