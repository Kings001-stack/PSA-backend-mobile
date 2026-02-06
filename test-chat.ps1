# Test Chat Endpoint
$baseUrl = "http://192.168.1.222:8000/api"

# Step 1: Login
Write-Host "Step 1: Logging in..." -ForegroundColor Cyan
$loginBody = @{
    email = 'admin@1.test'
    password = 'password'
    device_name = 'test-device'
} | ConvertTo-Json

$loginResponse = Invoke-WebRequest -Uri "$baseUrl/login" -Method POST -Body $loginBody -ContentType "application/json" -UseBasicParsing
$loginData = $loginResponse.Content | ConvertFrom-Json
$token = $loginData.access_token

Write-Host "✓ Login successful! Token: $($token.Substring(0, 20))..." -ForegroundColor Green

# Step 2: Send Chat Message
Write-Host "`nStep 2: Sending chat message..." -ForegroundColor Cyan
$chatBody = @{
    message = 'What medications do you have in stock?'
    session_id = 'test-session-123'
} | ConvertTo-Json

$headers = @{
    'Authorization' = "Bearer $token"
    'Content-Type' = 'application/json'
    'Accept' = 'application/json'
}

try {
    $chatResponse = Invoke-WebRequest -Uri "$baseUrl/chat/send" -Method POST -Body $chatBody -Headers $headers -UseBasicParsing -TimeoutSec 30
    $chatData = $chatResponse.Content | ConvertFrom-Json
    
    Write-Host "✓ Chat response received!" -ForegroundColor Green
    Write-Host "`nBot Response:" -ForegroundColor Yellow
    Write-Host $chatData.message -ForegroundColor White
    Write-Host "`nSession ID: $($chatData.session_id)" -ForegroundColor Gray
    Write-Host "Title: $($chatData.title)" -ForegroundColor Gray
}
catch {
    Write-Host "✗ Chat request failed!" -ForegroundColor Red
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.Exception.Response) {
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        $responseBody = $reader.ReadToEnd()
        Write-Host "Response: $responseBody" -ForegroundColor Red
    }
}
