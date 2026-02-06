<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\GeminiService;
use App\Http\Clients\GeminiClient;

echo "--- GEMINI AI TEST SCRIPT ---\n";

try {
    // 1. Check Configuration
    $apiKey = config('gemini.api_key');
    if (empty($apiKey)) {
        die("[FAIL] GEMINI_API_KEY is not set in .env or config.\n");
    }
    echo "[INFO] API Key found (ends in: ..." . substr($apiKey, -4) . ")\n";

    // 2. Initialize Service directly
    $client = app(GeminiClient::class);
    $gemini = new GeminiService($client);

    // 3. Attempt a real API call
    $prompt = "Respond with only one word: 'Online'";
    echo "[INFO] Sending test prompt to Gemini...\n";
    
    // We call generateResponse. If it returns the 'Notice' we know it hit a fallback.
    $response = $gemini->generateResponse($prompt);

    if (str_contains($response, '⚠️ System Notice')) {
        echo "\n[RESULT] Gemini is in BACKUP/SIMULATED mode.\n";
        echo "Reason: The API is likely hitting a quota limit or returning an error.\n";
        echo "Response received: " . $response . "\n";
    } else {
        echo "\n[PASS] Gemini is ONLINE! ✨\n";
        echo "Response received: " . $response . "\n";
    }

} catch (\Exception $e) {
    echo "\n[FAIL] An error occurred while testing Gemini:\n";
    echo $e->getMessage() . "\n";
}

echo "\n--- TEST COMPLETE ---\n";
