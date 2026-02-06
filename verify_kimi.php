<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\KimiService;
use App\Http\Clients\KimiClient;

echo "--- KIMI K2 (MOONSHOT) TEST SCRIPT ---\n";

try {
    // 1. Check Configuration
    $config = config('ai.kimi');
    $apiKey = $config['api_key'] ?? null;
    
    if (empty($apiKey)) {
        die("[FAIL] KIMI_API_KEY is not set in .env or config.\n");
    }
    
    $baseUrl = $config['base_url'] ?? 'N/A';
    echo "[INFO] API Key found (ends in: ..." . substr($apiKey, -4) . ")\n";
    echo "[INFO] Base URL: {$baseUrl}\n";

    // 2. Initialize Service directly
    $client = app(KimiClient::class);
    $kimi = new KimiService($client);

    // 3. Attempt a real API call
    $prompt = "Respond with only one word: 'Online'";
    echo "[INFO] Sending test prompt to Kimi...\n";
    
    $response = $kimi->generateResponse($prompt);

    if (str_contains($response, '⚠️ System Notice')) {
        echo "\n[RESULT] Kimi is in BACKUP/ESSENTIAL mode.\n";
        echo "Reason: The API is likely hitting a rate limit (429) or is temporarily unavailable.\n";
        echo "Response received: " . $response . "\n";
    } else {
        echo "\n[PASS] Kimi is ONLINE! 🚀\n";
        echo "Response received: " . $response . "\n";
    }

} catch (\Exception $e) {
    echo "\n[FAIL] An error occurred while testing Kimi:\n";
    echo $e->getMessage() . "\n";
}

echo "\n--- TEST COMPLETE ---\n";
