<?php

// Simple API test script
header('Content-Type: text/html; charset=utf-8');

$tenantToken = 'yuU58CC73UDUecRPBncK8xstV9n3AUdXNTt6JOPsEhMvfDOkd3IswJjkycKxTPEF';
$apiUrl = 'http://localhost/dashboard/chatbot/public/index.php/api/chat';

$data = [
    'message' => 'What is aspirin used for?',
    'customer_name' => 'Test User'
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-Tenant-Token: ' . $tenantToken
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

?>
<!DOCTYPE html>
<html>
<head>
    <title>API Test</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 20px; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .success { border-left: 4px solid #4caf50; }
        .error { border-left: 4px solid #f44336; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
        h2 { margin-top: 0; }
    </style>
</head>
<body>
    <h1>🧪 Pharmacy Chatbot API Test</h1>
    
    <div class="box">
        <h2>Request Details</h2>
        <p><strong>URL:</strong> <?php echo htmlspecialchars($apiUrl); ?></p>
        <p><strong>Method:</strong> POST</p>
        <p><strong>Tenant Token:</strong> <?php echo htmlspecialchars(substr($tenantToken, 0, 20)) . '...'; ?></p>
        <p><strong>Message:</strong> <?php echo htmlspecialchars($data['message']); ?></p>
    </div>
    
    <div class="box <?php echo $httpCode == 200 ? 'success' : 'error'; ?>">
        <h2>Response</h2>
        <p><strong>HTTP Code:</strong> <?php echo $httpCode; ?></p>
        
        <?php if ($error): ?>
            <p><strong>cURL Error:</strong> <?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        
        <p><strong>Response Body:</strong></p>
        <pre><?php 
            $decoded = json_decode($response, true);
            if ($decoded) {
                echo htmlspecialchars(json_encode($decoded, JSON_PRETTY_PRINT));
            } else {
                echo htmlspecialchars($response);
            }
        ?></pre>
    </div>
    
    <?php if ($httpCode == 200 && $decoded): ?>
        <div class="box success">
            <h2>✅ Success!</h2>
            <p>The API is working correctly.</p>
            <?php if (isset($decoded['message'])): ?>
                <p><strong>AI Response:</strong></p>
                <p><?php echo htmlspecialchars($decoded['message']); ?></p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="box error">
            <h2>❌ Error</h2>
            <p>The API returned an error. Check the response above for details.</p>
        </div>
    <?php endif; ?>
    
    <div class="box">
        <h2>Next Steps</h2>
        <ul>
            <li>If you see a 401 error: Check the tenant token</li>
            <li>If you see a 500 error: Check Laravel logs in <code>storage/logs/laravel.log</code></li>
            <li>If you see HTML instead of JSON: The route might not be found</li>
            <li>If successful: Try the <a href="test-chat.html">interactive chat interface</a></li>
        </ul>
    </div>
</body>
</html>
