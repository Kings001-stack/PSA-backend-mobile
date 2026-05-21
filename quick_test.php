<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🧪 Quick Analytics Test\n\n";

// Test database connection
try {
    $count = \App\Models\User::count();
    echo "✅ Database: Connected ({$count} users)\n";
} catch (\Exception $e) {
    echo "❌ Database: Failed - " . $e->getMessage() . "\n";
    exit(1);
}

// Test analytics controller
try {
    $admin = \App\Models\User::where('role', 'admin')->first();
    if (!$admin) {
        echo "❌ No admin user found\n";
        exit(1);
    }
    
    echo "✅ Admin User: {$admin->name}\n";
    
    // Simulate request
    $controller = new \App\Http\Controllers\Api\AnalyticsController();
    $request = new \Illuminate\Http\Request();
    $request->setUserResolver(function() use ($admin) {
        return $admin;
    });
    
    $response = $controller->index($request);
    $data = $response->getData(true);
    
    echo "✅ Analytics API: Working\n";
    echo "   • Conversations: " . ($data['conversations']['total'] ?? 0) . "\n";
    echo "   • Inventory Items: " . ($data['inventory']['total_items'] ?? 0) . "\n";
    echo "   • Total Users: " . ($data['overview']['total_users'] ?? 0) . "\n";
    
} catch (\Exception $e) {
    echo "❌ Analytics: Failed - " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✅ All tests passed!\n";
