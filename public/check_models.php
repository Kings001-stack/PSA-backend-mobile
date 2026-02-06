<?php
// Simple script to test Gemini Models
$envFile = __DIR__ . '/../.env';
$apiKey = '';

// 1. Get API Key from .env
if (file_exists($envFile)) {
    $lines = file($envFile);
    foreach ($lines as $line) {
        if (strpos(trim($line), 'GEMINI_API_KEY=') === 0) {
            $apiKey = trim(explode('=', $line, 2)[1]);
            break;
        }
    }
}

if (empty($apiKey)) {
    die("Error: Could not find GEMINI_API_KEY in .env file.");
}

// List of models to test
$models = [
    'gemini-pro',
    'gemini-1.0-pro',
    'gemini-1.5-flash',
    'gemini-1.5-flash-latest',
    'gemini-1.5-flash-001',
    'gemini-1.5-flash-8b',
    'gemini-1.5-pro',
    'gemini-1.5-pro-latest',
    'gemini-2.0-flash-lite',
    'gemini-2.0-flash-exp',
];

$versions = ['v1', 'v1beta'];

function testModel($version, $model, $apiKey) {
    $url = "https://generativelanguage.googleapis.com/{$version}/models/{$model}:generateContent?key={$apiKey}";
    
    $data = [
        "contents" => [
            [
                "parts" => [
                    ["text" => "Hello"]
                ]
            ]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Short timeout

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'code' => $httpCode,
        'response' => $response
    ];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gemini Model Tester</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background: #f0f0f0; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { margin-top: 0; }
        .key-display { background: #eee; padding: 10px; border-radius: 4px; font-family: monospace; word-break: break-all; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #f8f9fa; }
        .status-200 { color: green; font-weight: bold; }
        .status-404 { color: orange; }
        .status-429 { color: red; font-weight: bold; } /* Quota exceeded */
        .status-500 { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Gemini Model Availability</h1>
        <p>Testing API Key: <strong><?php echo substr($apiKey, 0, 10) . '...'; ?></strong></p>
        
        <table>
            <thead>
                <tr>
                    <th>Version</th>
                    <th>Model Name</th>
                    <th>Status</th>
                    <th>Message</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($versions as $version): ?>
                    <?php foreach ($models as $model): ?>
                        <?php 
                            $result = testModel($version, $model, $apiKey);
                            $code = $result['code'];
                            $class = 'status-' . $code;
                            $msg = '';
                            
                            $json = json_decode($result['response'], true);
                            if (isset($json['error']['message'])) {
                                $msg = $json['error']['message'];
                            } elseif ($code == 200) {
                                $msg = "Working!";
                            } else {
                                $msg = "HTTP " . $code;
                            }
                        ?>
                        <tr>
                            <td><?php echo $version; ?></td>
                            <td><?php echo $model; ?></td>
                            <td class="<?php echo $class; ?>"><?php echo $code; ?></td>
                            <td><?php echo htmlspecialchars(substr($msg, 0, 100)); ?></td>
                        </tr>
                        <?php flush(); ob_flush(); // Try to push output as we go ?>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
