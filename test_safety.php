<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\SafetyEngine;

$safety = new SafetyEngine();

$tests = [
    "how much Ibuprofen should I take" => true,
    "what is the dosage for Paracetamol" => true,
    "can i take amoxicillin while pregnant" => true,
    "i have chest pain and can't breathe" => true,
    "I want to speak to a pharmacist" => true,
    "how many tablets of panadol for my baby" => true,
    "what is the price of Ibuprofen" => false, // Should NOT escalate
    "where are you located" => false, // Should NOT escalate
];

echo "--- SAFETY ENGINE REGEX TEST ---\n";

foreach ($tests as $query => $shouldEscalate) {
    $result = $safety->check($query);
    $didEscalate = $result !== null;
    
    $status = ($didEscalate === $shouldEscalate) ? "[PASS]" : "[FAIL]";
    
    echo "{$status} Query: '{$query}'\n";
    if ($didEscalate) {
        echo "      Reason: {$result['reason']->value}\n";
    }
}

echo "\n--- TEST COMPLETE ---\n";
