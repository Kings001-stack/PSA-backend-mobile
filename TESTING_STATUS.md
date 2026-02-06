# Pharmacy Chatbot - Testing Status

## ✅ What's Working

### 1. Database & Models

-   All migrations successfully created and run
-   8 Eloquent models with relationships and tenant isolation
-   Test data seeded (2 tenants, 6 users, 10 medications)

### 2. API Infrastructure

-   Basic API endpoint working (`/api/test`)
-   Tenant authentication working (`/api/test-auth`)
-   Conversation and message creation working (`/api/test-chat`)
-   Middleware properly configured (tenant auth, rate limiting)

### 3. Test Endpoints Created

-   `GET /api/test` - Basic API health check
-   `GET /api/test-auth` - Tests tenant authentication
-   `POST /api/test-chat` - Tests full flow without Gemini API

### 4. Configuration

-   Switched from Redis to database for cache/queue (Redis not installed on XAMPP)
-   Fixed SafetyEngine enum array key issue
-   Gemini model updated to `gemini-2.0-flash-exp`

## ⚠️ Current Issue

### Main Chat Endpoint (`/api/chat`) - Connection Timeout

The full chat endpoint that calls the Gemini API is timing out. This is likely due to:

1. Gemini API taking too long to respond
2. Possible issue with the Gemini API key or request format
3. PHP/Apache timeout settings

## 🧪 How to Test

### 1. Test Basic API

Open in browser:

```
http://localhost/dashboard/chatbot/public/index.php/api/test
```

Expected: JSON response with success message

### 2. Test Tenant Authentication

```powershell
$headers = @{ "X-Tenant-Token" = "yuU58CC73UDUecRPBncK8xstV9n3AUdXNTt6JOPsEhMvfDOkd3IswJjkycKxTPEF" }
Invoke-WebRequest -Uri "http://localhost/dashboard/chatbot/public/index.php/api/test-auth" -Headers $headers -UseBasicParsing
```

Expected: JSON with tenant details

### 3. Test Chat Without Gemini

```powershell
$headers = @{ "Content-Type" = "application/json"; "X-Tenant-Token" = "yuU58CC73UDUecRPBncK8xstV9n3AUdXNTt6JOPsEhMvfDOkd3IswJjkycKxTPEF" }
$body = '{"message":"What is aspirin?","customer_name":"Test User"}'
Invoke-WebRequest -Uri "http://localhost/dashboard/chatbot/public/index.php/api/test-chat" -Method POST -Headers $headers -Body $body -UseBasicParsing
```

Expected: JSON with mock response and conversation ID

### 4. Test Gemini API Directly

Open in browser:

```
http://localhost/dashboard/chatbot/public/test-gemini.php
```

This will test the Gemini API connection directly and show any errors.

### 5. Test Chat Interface

Open in browser:

```
http://localhost/dashboard/chatbot/public/test-chat.html
```

-   Enter tenant token: `yuU58CC73UDUecRPBncK8xstV9n3AUdXNTt6JOPsEhMvfDOkd3IswJjkycKxTPEF`
-   Enter your name
-   Start chatting (currently uses test endpoint without Gemini)

## 📝 Test Credentials

### Tenant 1: HealthPlus Pharmacy

-   **Token:** `yuU58CC73UDUecRPBncK8xstV9n3AUdXNTt6JOPsEhMvfDOkd3IswJjkycKxTPEF`
-   **Email:** admin@healthplus.com
-   **Admin User:** admin@1.test / password

### Tenant 2: CareWell Pharmacy

-   **Token:** Check database `tenants` table
-   **Email:** admin@carewell.com
-   **Admin User:** admin@2.test / password

## 🔧 Next Steps

1. **Test Gemini API Connection**

    - Open `http://localhost/dashboard/chatbot/public/test-gemini.php`
    - Check if the API key is valid
    - Verify the model name is correct
    - Check for any error messages

2. **If Gemini Works:**

    - The issue might be with the RAG pipeline or service dependencies
    - Check `storage/logs/laravel.log` for detailed errors
    - May need to increase PHP timeout settings

3. **If Gemini Fails:**

    - Verify API key is correct in `.env`
    - Check if the Gemini API endpoint is accessible
    - Try a different model name (e.g., `gemini-1.5-flash`)

4. **Once Gemini is Working:**
    - Update `test-chat.html` to use `/api/chat` instead of `/api/test-chat`
    - Test the full chat flow with AI responses
    - Add medications and FAQ documents via admin endpoints
    - Test vector search and RAG pipeline

## 📂 Important Files

-   **Test Files:**

    -   `public/test-api.php` - Detailed API test with cURL
    -   `public/test-gemini.php` - Direct Gemini API test
    -   `public/test-chat.html` - Interactive chat interface
    -   `TEST_API.md` - API documentation

-   **Configuration:**

    -   `.env` - Environment variables (API keys, database)
    -   `config/gemini.php` - Gemini API configuration
    -   `config/pinecone.php` - Vector database configuration

-   **Logs:**
    -   `storage/logs/laravel.log` - Application errors and logs

## 🐛 Known Issues Fixed

1. ✅ Redis not installed - Switched to database cache/queue
2. ✅ SafetyEngine enum array keys - Changed to string keys
3. ✅ Tenant token not generated - Fixed seeder to manually generate tokens
4. ✅ Migration order - Combined tenants with users migration
5. ✅ Gemini model name - Updated to `gemini-2.0-flash-exp`

## 💡 Tips

-   Always check `storage/logs/laravel.log` for errors
-   Use `php artisan config:clear` after changing `.env`
-   Use `php artisan migrate:fresh --seed` to reset database
-   Test endpoints in order: basic → auth → test-chat → full chat
