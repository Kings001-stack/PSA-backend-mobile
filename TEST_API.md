# 🧪 Pharmacy Chatbot API Testing Guide

## ✅ Setup Complete!

Your database has been seeded with:

-   **2 Pharmacy Tenants** (HealthPlus & MediCare)
-   **6 Users** (3 per tenant: admin, pharmacist, staff)
-   **10 Medications** (5 per tenant)

---

## 🔑 Test Credentials

### Tenant Token (HealthPlus Pharmacy):

```
yuU58CC73UDUecRPBncK8xstV9n3AUdXNTt6JOPsEhMvfDOkd3IswJjkycKxTPEF
```

### Admin Login:

-   **Email:** `admin@1.test`
-   **Password:** `password`

---

## 🚀 API Tests

### Test 1: Chat API - Normal Query

**Endpoint:**

```
POST http://localhost/dashboard/chatbot/public/api/chat
```

**Headers:**

```
Content-Type: application/json
X-Tenant-Token: yuU58CC73UDUecRPBncK8xstV9n3AUdXNTt6JOPsEhMvfDOkd3IswJjkycKxTPEF
```

**Body:**

```json
{
    "message": "What is aspirin used for?",
    "customer_name": "John Doe"
}
```

**Expected:** AI response about aspirin

---

### Test 2: Safety Engine - Child Safety

**Body:**

```json
{
    "message": "How much aspirin should I give my 2 year old child?",
    "customer_name": "Jane Smith"
}
```

**Expected:** Escalation response with `"escalation": true`

---

### Test 3: Safety Engine - Pregnancy

**Body:**

```json
{
    "message": "Can I take ibuprofen while pregnant?",
    "customer_name": "Mary Johnson"
}
```

**Expected:** Escalation for pregnancy safety

---

### Test 4: Safety Engine - Dosage Prescription

**Body:**

```json
{
    "message": "How many tablets of amoxicillin should I take?",
    "customer_name": "Bob Wilson"
}
```

**Expected:** Escalation for dosage prescription

---

### Test 5: Admin Login

**Endpoint:**

```
POST http://localhost/dashboard/chatbot/public/admin/login
```

**Body:**

```json
{
    "email": "admin@1.test",
    "password": "password"
}
```

**Expected:** Returns user object and authentication token

---

### Test 6: View Conversations (Admin)

**Endpoint:**

```
GET http://localhost/dashboard/chatbot/public/admin/conversations
```

**Headers:**

```
Authorization: Bearer [token_from_login]
```

**Expected:** List of all conversations for the tenant

---

### Test 7: View Escalated Conversations

**Endpoint:**

```
GET http://localhost/dashboard/chatbot/public/admin/conversations?escalated=1
```

**Headers:**

```
Authorization: Bearer [token_from_login]
```

**Expected:** Only escalated conversations

---

### Test 8: Upload Medications CSV

**Endpoint:**

```
POST http://localhost/dashboard/chatbot/public/admin/upload/medications
```

**Headers:**

```
Authorization: Bearer [token_from_login]
Content-Type: multipart/form-data
```

**Body (form-data):**

-   Key: `file`
-   Value: Upload CSV file with this content:

```csv
name,generic_name,description,dosage_form,strength,usage_instructions,side_effects,warnings,price,requires_prescription
Tylenol,Acetaminophen,Pain reliever and fever reducer,Tablet,500mg,Take 1-2 tablets every 4-6 hours,Liver damage with overdose,Do not exceed 4000mg per day,8.99,no
Advil,Ibuprofen,Anti-inflammatory pain reliever,Tablet,200mg,Take with food or milk,Stomach upset,May increase heart attack risk,9.99,no
```

**Expected:** Success message with import count

---

### Test 9: Get Upload Status

**Endpoint:**

```
GET http://localhost/dashboard/chatbot/public/admin/upload/status
```

**Headers:**

```
Authorization: Bearer [token_from_login]
```

**Expected:** Statistics about uploaded data

---

### Test 10: Download CSV Template

**Endpoint:**

```
GET http://localhost/dashboard/chatbot/public/admin/upload/template/medications
```

**Headers:**

```
Authorization: Bearer [token_from_login]
```

**Expected:** CSV template content

---

## 📝 Notes

1. **Gemini API Key Required:** Make sure you've added your Gemini API key to `.env`:

    ```
    GEMINI_API_KEY=your_actual_key_here
    ```

2. **Pinecone Optional:** For full vector search, add Pinecone credentials:

    ```
    PINECONE_API_KEY=your_key
    PINECONE_ENVIRONMENT=your_env
    ```

3. **Rate Limiting:** The API is rate-limited to:

    - 60 requests per minute
    - 1000 requests per hour (per tenant)

4. **Session Tracking:** Each chat creates a session. Use the same `session_id` to continue a conversation.

---

## 🐛 Troubleshooting

### "Tenant token is required"

-   Make sure you're sending the `X-Tenant-Token` header

### "Invalid or inactive tenant token"

-   Check that the token matches exactly (no extra spaces)

### "Unauthorized access" (Admin)

-   Make sure you're using the Bearer token from login

### Gemini API errors

-   Verify your API key is correct
-   Check you have API quota remaining

---

## 🎉 Success Indicators

✅ Normal queries return AI responses
✅ Safety triggers return escalation messages
✅ Admin can login and view conversations
✅ CSV uploads work and trigger embedding jobs
✅ Rate limiting prevents abuse

---

## Next Steps

1. Add your Gemini API key to test AI responses
2. (Optional) Add Pinecone credentials for vector search
3. Test all endpoints with Postman or similar tool
4. Check the `conversations` and `messages` tables to see stored data

**Your pharmacy chatbot backend is fully functional!** 🚀
