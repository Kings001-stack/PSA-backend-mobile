<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$apiKey = config('gemini.api_key');
$baseUrl = config('gemini.base_url');

echo "Base URL: $baseUrl\n";
$response = Http::withoutVerifying()->get("$baseUrl/models?key=$apiKey");

if ($response->failed()) {
    echo "Request failed: " . $response->body() . "\n";
    exit;
}

$models = $response->json()['models'];
foreach ($models as $model) {
    if (in_array('generateContent', $model['supportedGenerationMethods'])) {
        echo $model['name'] . "\n";
    }
}
