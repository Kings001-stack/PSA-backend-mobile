<?php

// Simple AI Provider Test (No Laravel Dependencies)

echo "===========================================\n";
echo "AI PROVIDER TESTING SCRIPT\n";
echo "===========================================\n\n";

// Load .env file manually
$envFile = __DIR__ . '/.env';
$envVars = [];
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        $envVars[trim($key)] = trim($value);
    }
}

// Test message
$testMessage = "Hello! Can you tell me about paracetamol in one sentence?";

// Test 1: Gemini
echo "1. TESTING GEMINI\n";
echo "-------------------------------------------\n";
try {
    $geminiKey = $envVars['GEMINI_API_KEY'] ?? '';
    $geminiModel = $envVars['GEMINI_MODEL'] ?? 'gemini-2.0-flash';
    
    if (empty($geminiKey)) {
        echo "❌ STATUS: FAILED - No API key found\n\n";
    } else {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$geminiModel}:generateContent?key={$geminiKey}";
        
        $data = json_encode([
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
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $startTime = microtime(true);
        $response = curl_exec($ch);
        $responseTime = round(microtime(true) - $startTime, 2);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $result = json_decode($response, true);
            $reply = $result['candidates'][0]['content']['parts'][0]['text'] ?? 'No response';
            echo "✅ STATUS: SUCCESS\n";
            echo "📝 RESPONSE: " . substr($reply, 0, 200) . "...\n";
            echo "⏱️  RESPONSE TIME: {$responseTime}s\n";
        } else {
            echo "❌ STATUS: FAILED (HTTP {$httpCode})\n";
            echo "ERROR: " . substr($response, 0, 200) . "\n";
        }
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
    $kimiKey = $envVars['KIMI_API_KEY'] ?? '';
    $kimiModel = $envVars['KIMI_MODEL'] ?? 'moonshotai/kimi-k2:free';
    
    if (empty($kimiKey)) {
        echo "❌ STATUS: FAILED - No API key found\n\n";
    } else {
        $url = 'https://openrouter.ai/api/v1/chat/completions';
        
        $data = json_encode([
            'model' => $kimiModel,
            'messages' => [
                ['role' => 'user', 'content' => $testMessage]
            ],
            'temperature' => 0.7,
            'max_tokens' => 500,
        ]);
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$kimiKey}",
            'Content-Type: application/json',
            'HTTP-Referer: http://localhost',
            'X-Title: Pharmacy Chatbot',
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        
        $startTime = microtime(true);
        $response = curl_exec($ch);
        $responseTime = round(microtime(true) - $startTime, 2);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $result = json_decode($response, true);
            $reply = $result['choices'][0]['message']['content'] ?? 'No response';
            echo "✅ STATUS: SUCCESS\n";
            echo "📝 RESPONSE: " . substr($reply, 0, 200) . "...\n";
            echo "⏱️  RESPONSE TIME: {$responseTime}s\n";
        } else {
            echo "❌ STATUS: FAILED (HTTP {$httpCode})\n";
            echo "ERROR: " . substr($response, 0, 200) . "\n";
        }
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
    $openrouterKey = $envVars['OPENROUTER_API_KEY'] ?? '';
    $openrouterModel = $envVars['OPENROUTER_MODEL'] ?? 'nvidia/nemotron-3-nano-30b-a3b:free';
    
    if (empty($openrouterKey)) {
        echo "❌ STATUS: FAILED - No API key found\n\n";
    } else {
        $url = 'https://openrouter.ai/api/v1/chat/completions';
        
        $data = json_encode([
            'model' => $openrouterModel,
            'messages' => [
                ['role' => 'user', 'content' => $testMessage]
            ],
            'temperature' => 0.7,
            'max_tokens' => 500,
        ]);
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$openrouterKey}",
            'Content-Type: application/json',
            'HTTP-Referer: http://localhost',
            'X-Title: Pharmacy Chatbot',
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        
        $startTime = microtime(true);
        $response = curl_exec($ch);
        $responseTime = round(microtime(true) - $startTime, 2);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $result = json_decode($response, true);
            $reply = $result['choices'][0]['message']['content'] ?? 'No response';
            echo "✅ STATUS: SUCCESS\n";
            echo "📝 RESPONSE: " . substr($reply, 0, 200) . "...\n";
            echo "⏱️  RESPONSE TIME: {$responseTime}s\n";
        } else {
            echo "❌ STATUS: FAILED (HTTP {$httpCode})\n";
            echo "ERROR: " . substr($response, 0, 200) . "\n";
        }
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
echo "\nCURRENT AI_PROVIDER: " . ($envVars['AI_PROVIDER'] ?? 'not set') . "\n";
