<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\AIServiceFactory;
use Illuminate\Support\Facades\Config;

echo "Current Provider Config: " . config('ai.provider') . "\n";

$provider = AIServiceFactory::make();
echo "Resolved Provider Class: " . get_class($provider) . "\n";
echo "Provider Name: " . $provider->getProviderName() . "\n";
echo "Is Available: " . ($provider->isAvailable() ? 'Yes' : 'No') . "\n";

echo "\n--- Switching to Kimi (Simulation) ---\n";
Config::set('ai.provider', 'kimi');
$kimiProvider = AIServiceFactory::make();
echo "Resolved Provider Class: " . get_class($kimiProvider) . "\n";
echo "Provider Name: " . $kimiProvider->getProviderName() . "\n";

echo "\n--- Switching to Gemini (Simulation) ---\n";
Config::set('ai.provider', 'gemini');
$geminiProvider = AIServiceFactory::make();
echo "Resolved Provider Class: " . get_class($geminiProvider) . "\n";
echo "Provider Name: " . $geminiProvider->getProviderName() . "\n";
