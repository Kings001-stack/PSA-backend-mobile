<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Http;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "===========================================\n";
echo "AI PROVIDER TESTING SCRIPT\n";
echo "===========================================\n\n";

// Test message
$testMessage = "Hello! Can you tell me about paracetamol?";

// Test 1: Gemini
echo "1. TESTING GEMINI\n";
echo "-------------------------------------------\n";
try {
    $geminiKey = $_ENV['GEMINI_API_KEY'];
    $geminiModel = $_ENV['GEMINI_MODEL'] ?? 'gemini-2.0-flash';
    
    $response = Http::timeout(30)
        ->withHeaders([
            'Content-Type' => 'application/json',
        ])
        ->post("https://generativelanguage.googleapis.com/v1beta/models/{$geminiModel}:generateContent?key={$geminiKey}", [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $testMessage]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 500,
            ]
        ]);

    if ($response->successful()) {
        $data = $response->json();
        $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'No response';
        echo "✅ STATUS: SUCCESS\n";
        echo "📝 RESPONSE: " . substr($reply, 0, 200) . "...\n";
        echo "⏱️  RESPONSE TIME: " . $response->handlerStats()['total_time'] . "s\n";
    } else {
        echo "❌ STATUS: FAILED\n";
        echo "ERROR: " . $response->body() . "\n";
    }
} catch (Exception $e) {
    echo "❌ STATUS: ERROR\n";
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Kimi (via OpenRouter)
echo "2. TESTING KIMI (via OpenRouter)\n";
echo "-------------------------------------------\n";
try {
    $kimiKey = $_ENV['KIMI_API_KEY'];
    $kimiModel = $_ENV['KIMI_MODEL'] ?? 'moonshotai/kimi-k2:free';
    
    $response = Http::timeout(60)
        ->withHeaders([
            'Authorization' => "Bearer {$kimiKey}",
            'Content-Type' => 'application/json',
            'HTTP-Referer' => 'http://localhost',
            'X-Title' => 'Pharmacy Chatbot',
        ])
        ->post('https://openrouter.ai/api/v1/chat/completions', [
            'model' => $kimiModel,
            'messages' => [
                ['role' => 'user', 'content' => $testMessage]
            ],
            'temperature' => 0.7,
            'max_tokens' => 500,
        ]);

    if ($response->successful()) {
        $data = $response->json();
        $reply = $data['choices'][0]['message']['content'] ?? 'No response';
        echo "✅ STATUS: SUCCESS\n";
        echo "📝 RESPONSE: " . substr($reply, 0, 200) . "...\n";
        echo "⏱️  RESPONSE TIME: " . $response->handlerStats()['total_time'] . "s\n";
    } else {
        echo "❌ STATUS: FAILED\n";
        echo "ERROR: " . $response->body() . "\n";
    }
} catch (Exception $e) {
    echo "❌ STATUS: ERROR\n";
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: OpenRouter (Nemotron)
echo "3. TESTING OPENROUTER (Nemotron)\n";
echo "-------------------------------------------\n";
try {
    $openrouterKey = $_ENV['OPENROUTER_API_KEY'];
    $openrouterModel = $_ENV['OPENROUTER_MODEL'] ?? 'nvidia/nemotron-3-nano-30b-a3b:free';
    
    $response = Http::timeout(60)
        ->withHeaders([
            'Authorization' => "Bearer {$openrouterKey}",
            'Content-Type' => 'application/json',
            'HTTP-Referer' => 'http://localhost',
            'X-Title' => 'Pharmacy Chatbot',
        ])
        ->post('https://openrouter.ai/api/v1/chat/completions', [
            'model' => $openrouterModel,
            'messages' => [
                ['role' => 'user', 'content' => $testMessage]
            ],
            'temperature' => 0.7,
            'max_tokens' => 500,
        ]);

    if ($response->successful()) {
        $data = $response->json();
        $reply = $data['choices'][0]['message']['content'] ?? 'No response';
        echo "✅ STATUS: SUCCESS\n";
        echo "📝 RESPONSE: " . substr($reply, 0, 200) . "...\n";
        echo "⏱️  RESPONSE TIME: " . $response->handlerStats()['total_time'] . "s\n";
    } else {
        echo "❌ STATUS: FAILED\n";
        echo "ERROR: " . $response->body() . "\n";
    }
} catch (Exception $e) {
    echo "❌ STATUS: ERROR\n";
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n";
echo "===========================================\n";
echo "TESTING COMPLETE\n";
echo "===========================================\n";
echo "\nRECOMMENDATION:\n";
echo "- Use the provider that shows ✅ SUCCESS\n";
echo "- Consider response time for better UX\n";
echo "- Update AI_PROVIDER in .env file\n";
