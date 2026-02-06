<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\OpenRouterService;
use App\Http\Clients\OpenRouterClient;

echo "--- OPENROUTER TEST SCRIPT ---\n";

try {
    // 1. Check Configuration
    $config = config('ai.openrouter');
    $apiKey = $config['api_key'] ?? null;
    
    if (empty($apiKey)) {
        die("[FAIL] OPENROUTER_API_KEY is not set.\n");
    }
    
    $model = $config['model'] ?? 'N/A';
    echo "[INFO] API Key found (ends in: ..." . substr($apiKey, -4) . ")\n";
    echo "[INFO] Model: {$model}\n";

    // 2. Initialize Service
    $client = app(OpenRouterClient::class);
    $or = new OpenRouterService($client);

    // 3. API call
    $prompt = "Respond with one word: 'READY'";
    echo "[INFO] Sending test prompt to OpenRouter...\n";
    
    $response = $or->generateResponse($prompt);

    if (str_contains($response, '⚠️ System Notice')) {
        echo "\n[RESULT] OpenRouter is in BACKUP mode.\n";
        echo "Response: " . $response . "\n";
    } else {
        echo "\n[PASS] OpenRouter is ONLINE! ⚡\n";
        echo "Response: " . $response . "\n";
    }

} catch (\Exception $e) {
    echo "\n[FAIL] Error: " . $e->getMessage() . "\n";
}

echo "\n--- TEST COMPLETE ---\n";
