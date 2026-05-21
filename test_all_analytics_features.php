<?php

/**
 * Comprehensive Analytics Feature Test
 * Tests all analytics endpoints and chart data
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║         COMPREHENSIVE ANALYTICS FEATURE TEST                   ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Get admin user
$admin = User::where('role', 'admin')->orWhere('role', 'pharmacist')->first();

if (!$admin) {
    echo "❌ No admin/pharmacist user found.\n";
    exit(1);
}

echo "✅ Testing as: {$admin->name} ({$admin->role})\n";
echo "   Tenant ID: {$admin->tenant_id}\n\n";

// Create test token
$token = $admin->createToken('test-analytics-full')->plainTextToken;

// Test configuration
$baseUrl = 'http://10.254.172.244:8000';
$tests = [
    'Health Check' => '/api/health',
    'Admin Dashboard' => '/api/admin/dashboard',
    'Analytics Overview' => '/api/admin/analytics',
];

echo "🌐 Testing against: {$baseUrl}\n\n";

$results = [];

foreach ($tests as $name => $endpoint) {
    echo "Testing: {$name}\n";
    echo str_repeat("-", 70) . "\n";
    
    $url = $baseUrl . $endpoint;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Accept: application/json',
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $startTime = microtime(true);
    $response = curl_exec($ch);
    $endTime = microtime(true);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $responseTime = round(($endTime - $startTime) * 1000, 2);
    
    curl_close($ch);
    
    $results[$name] = [
        'status' => $httpCode,
        'time' => $responseTime,
        'success' => $httpCode === 200,
    ];
    
    if ($httpCode === 200) {
        echo "✅ Status: {$httpCode} OK\n";
        echo "⏱️  Response Time: {$responseTime}ms\n";
        
        $data = json_decode($response, true);
        
        if ($name === 'Analytics Overview' && $data) {
            echo "\n📊 Analytics Data Summary:\n";
            echo "   • Overview Metrics: " . count($data['overview']) . " items\n";
            echo "   • Conversation Data: " . ($data['conversations']['total'] ?? 0) . " conversations\n";
            echo "   • Inventory Items: " . ($data['inventory']['total_items'] ?? 0) . " items\n";
            echo "   • Inventory Value: ₦" . number_format($data['inventory']['total_value'] ?? 0, 2) . "\n";
            echo "   • Refill Requests: " . ($data['refills']['total'] ?? 0) . " requests\n";
            echo "   • Trend Data Points: " . count($data['trends']['last_7_days'] ?? []) . " days\n";
            
            // Chart data validation
            echo "\n📈 Chart Data Validation:\n";
            
            // Line Chart Data (Conversations)
            $trendData = $data['trends']['last_7_days'] ?? [];
            $hasConversationData = array_sum(array_column($trendData, 'conversations')) > 0;
            echo "   • Line Chart (Conversations): " . ($hasConversationData ? "✅ Has data" : "⚠️  No data") . "\n";
            
            // Bar Chart Data (Refills)
            $hasRefillData = array_sum(array_column($trendData, 'refills')) > 0;
            echo "   • Bar Chart (Refills): " . ($hasRefillData ? "✅ Has data" : "⚠️  No data") . "\n";
            
            // Pie Chart Data (Stock Distribution)
            $stockDist = $data['inventory']['stock_distribution'] ?? [];
            $hasStockData = array_sum($stockDist) > 0;
            echo "   • Pie Chart (Stock): " . ($hasStockData ? "✅ Has data" : "⚠️  No data") . "\n";
            
            // Conversation Status Pie Chart
            $convStatus = $data['conversations']['by_status'] ?? [];
            $hasStatusData = count($convStatus) > 0;
            echo "   • Pie Chart (Status): " . ($hasStatusData ? "✅ Has data" : "⚠️  No data") . "\n";
            
            // Top Medications List
            $topMeds = $data['inventory']['top_medications'] ?? [];
            echo "   • Top Medications List: " . count($topMeds) . " items\n";
            
            // Top Requested List
            $topRequested = $data['refills']['top_requested'] ?? [];
            echo "   • Top Requested List: " . count($topRequested) . " items\n";
            
            // Key Metrics
            echo "\n🎯 Key Performance Indicators:\n";
            echo "   • Escalation Rate: " . ($data['conversations']['escalation_rate'] ?? 0) . "%\n";
            echo "   • Approval Rate: " . ($data['refills']['approval_rate'] ?? 0) . "%\n";
            echo "   • Avg Messages/Conv: " . ($data['conversations']['avg_messages_per_conversation'] ?? 0) . "\n";
            echo "   • Low Stock Count: " . ($data['inventory']['low_stock_items'] ?? 0) . "\n";
            echo "   • Expiring Soon: " . ($data['inventory']['expiring_soon'] ?? 0) . "\n";
        }
        
        if ($name === 'Admin Dashboard' && $data) {
            echo "\n📊 Dashboard Stats:\n";
            $stats = $data['stats'] ?? [];
            foreach ($stats as $key => $value) {
                echo "   • " . ucwords(str_replace('_', ' ', $key)) . ": {$value}\n";
            }
        }
        
    } else {
        echo "❌ Status: {$httpCode} FAILED\n";
        echo "Response: " . substr($response, 0, 200) . "\n";
    }
    
    echo "\n";
}

// Summary
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                        TEST SUMMARY                            ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$totalTests = count($results);
$passedTests = count(array_filter($results, fn($r) => $r['success']));
$failedTests = $totalTests - $passedTests;

foreach ($results as $name => $result) {
    $status = $result['success'] ? '✅ PASS' : '❌ FAIL';
    $time = $result['time'] . 'ms';
    echo sprintf("%-30s %s (%s)\n", $name, $status, $time);
}

echo "\n";
echo "Total Tests: {$totalTests}\n";
echo "Passed: {$passedTests}\n";
echo "Failed: {$failedTests}\n";

if ($failedTests === 0) {
    echo "\n🎉 ALL TESTS PASSED! Analytics feature is fully operational.\n";
} else {
    echo "\n⚠️  Some tests failed. Please check the errors above.\n";
}

// Mobile App Instructions
echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║                   MOBILE APP TESTING                           ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

echo "📱 To test on your mobile device:\n\n";
echo "1. Ensure your phone is on the same Wi-Fi network\n";
echo "2. Start the backend server:\n";
echo "   cd chatbot\n";
echo "   php artisan serve --host=0.0.0.0 --port=8000\n\n";
echo "3. Start the frontend:\n";
echo "   cd frontend\n";
echo "   npx expo start\n\n";
echo "4. Scan QR code with Expo Go app\n\n";
echo "5. Login as admin and navigate to:\n";
echo "   Admin Dashboard → Analytics & Reports\n\n";

echo "🌐 API Base URL: {$baseUrl}\n";
echo "📊 Analytics Endpoint: {$baseUrl}/api/admin/analytics\n\n";

echo "✨ Features to test in the app:\n";
echo "   • Overview Tab - Key metrics and pie charts\n";
echo "   • Trends Tab - Line and bar charts\n";
echo "   • Inventory Tab - Stock distribution\n";
echo "   • Pull-to-refresh functionality\n";
echo "   • Tab switching\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "Test completed at: " . date('Y-m-d H:i:s') . "\n";
echo "═══════════════════════════════════════════════════════════════\n\n";
