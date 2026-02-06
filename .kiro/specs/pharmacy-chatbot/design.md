# Design Document

## Overview

The Multi-Tenant AI Pharmacy Chatbot Platform is a SaaS application built with Laravel backend and Vue.js frontend widget. The system uses Google Gemini API for AI generation, implements RAG (Retrieval Augmented Generation) with pgvector for semantic search, and enforces strict multi-tenancy with data isolation. Each pharmacy tenant can embed a chat widget on their website to provide AI-powered medication guidance to customers.

## Architecture

### High-Level Architecture

```
┌─────────────────┐
│  Pharmacy Site  │
│   (Customer)    │
└────────┬────────┘
         │ Embeds Widget
         ▼
┌─────────────────┐
│   Vue.js Widget │ ◄─── Built & Hosted as CDN Asset
│   (chat-widget) │
└────────┬────────┘
         │ HTTPS/JSON
         ▼
┌─────────────────────────────────────┐
│         Laravel API Backend         │
│  ┌──────────────────────────────┐  │
│  │   Chat Controller            │  │
│  │   ├─ Safety Engine           │  │
│  │   ├─ RAG Pipeline            │  │
│  │   └─ Gemini Integration      │  │
│  ├──────────────────────────────┤  │
│  │   Admin Controller           │  │
│  │   Tenant Service             │  │
│  │   Embedding Service (Queue)  │  │
│  └──────────────────────────────┘  │
└────────┬────────────────────┬──────┘
         │                    │
         ▼                    ▼
┌─────────────────┐  ┌──────────────────┐
│   PostgreSQL    │  │  pgvector        │
│   (Tenants,     │  │  (Embeddings)    │
│    Messages,    │  │  Per-tenant      │
│    Inventory)   │  │  namespaces      │
└─────────────────┘  └──────────────────┘
         │
         ▼
┌─────────────────┐
│  Google Gemini  │
│  API (Gen + Emb)│
└─────────────────┘
```

### Technology Stack

**Backend:**

-   Laravel 11 (PHP 8.2+)
-   PostgreSQL with pgvector extension
-   Laravel Queues (database driver for MVP)
-   Google Gemini API (gemini-pro, text-embedding-004)

**Frontend:**

-   Vue.js 3 (Composition API)
-   Vite for bundling
-   Tailwind CSS for styling
-   Hosted as static CDN asset

## Components and Interfaces

### 1. Vue.js Chat Widget

**Purpose:** Embeddable chat interface for pharmacy websites

**Key Components:**

-   `ChatWidget.vue` - Main container
-   `MessageList.vue` - Display conversation
-   `MessageInput.vue` - User input field
-   `TypingIndicator.vue` - Loading state
-   `EscalationBanner.vue` - Human handoff notification

**Configuration Interface:**

```javascript
window.PharmaiWidgetConfig = {
    tenantToken: string,
    apiBase: string,
    branding: {
        primaryColor: string,
        pharmacyName: string,
        welcomeMessage: string,
    },
};
```

### 2. Chat Controller (Laravel)

**Endpoint:** `POST /api/tenant/chat`

**Responsibilities:**

-   Authenticate tenant via token
-   Validate and sanitize input
-   Execute Safety Engine
-   Trigger RAG Pipeline
-   Call Gemini API
-   Store conversation
-   Return response

**Request/Response:**

```php
// Request
{
  "message": "Do you have Augmentin 625mg?",
  "session_id": "uuid-optional",
  "tenant_token": "abc123"
}

// Response
{
  "response": "Yes, we have Augmentin 625mg in stock...",
  "escalation": false,
  "session_id": "uuid"
}
```

### 3. RAG Pipeline Service

**Purpose:** Retrieve relevant context before AI generation

**Pipeline Steps:**

1. **Entity Extraction** - Parse medication names from user message
2. **Vector Search** - Query pgvector for similar documents
3. **Context Building** - Format retrieved data into prompt context
4. **Prompt Composition** - Combine system prompt + context + user message
5. **Gemini Call** - Generate response with context

**Implementation:**

```php
class RagPipelineService
{
    public function process(string $message, int $tenantId): array
    {
        $entities = $this->extractEntities($message);
        $documents = $this->vectorSearch($entities, $tenantId);
        $context = $this->buildContext($documents);
        return ['context' => $context, 'entities' => $entities];
    }
}
```

### 4. Safety Engine

**Purpose:** Prevent unsafe medical advice

**Trigger Patterns:**

-   Dosage prescription requests
-   Child/pregnancy medication queries
-   Controlled substance requests
-   Diagnosis attempts

**Implementation:**

```php
class SafetyEngine
{
    private array $escalationPatterns = [
        '/how much (should|can) (i|my child) take/i',
        '/prescribe|prescription/i',
        '/diagnose|diagnosis/i',
        '/pregnant|pregnancy|breastfeeding/i',
    ];

    public function check(string $message): bool
    {
        foreach ($this->escalationPatterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return true; // Escalation needed
            }
        }
        return false;
    }
}
```

### 5. Embedding Service

**Purpose:** Generate and store vector embeddings

**Process:**

-   Triggered via Laravel Queue when data uploaded
-   Chunks documents into manageable sizes
-   Calls Gemini Embedding API
-   Stores in pgvector with tenant namespace

**Implementation:**

```php
class EmbeddingJob implements ShouldQueue
{
    public function handle(GeminiService $gemini, VectorStore $vectorStore)
    {
        $chunks = $this->chunkDocument($this->document);

        foreach ($chunks as $chunk) {
            $embedding = $gemini->generateEmbedding($chunk);
            $vectorStore->store([
                'tenant_id' => $this->tenantId,
                'content' => $chunk,
                'embedding' => $embedding,
                'metadata' => $this->metadata
            ]);
        }
    }
}
```

### 6. Tenant Service

**Purpose:** Manage multi-tenant operations

**Key Methods:**

-   `createTenant()` - Provision new pharmacy
-   `validateToken()` - Authenticate requests
-   `getTenantContext()` - Load tenant-specific config
-   `isolateQuery()` - Apply tenant_id filters

## Data Models

### Database Schema

**tenants**

```sql
CREATE TABLE tenants (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    tenant_token VARCHAR(64) UNIQUE NOT NULL,
    vector_namespace VARCHAR(100) UNIQUE NOT NULL,
    db_mode VARCHAR(20) DEFAULT 'shared',
    branding JSONB,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**users**

```sql
CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT REFERENCES tenants(id),
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'admin',
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**conversations**

```sql
CREATE TABLE conversations (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT REFERENCES tenants(id),
    session_id UUID UNIQUE NOT NULL,
    customer_identifier VARCHAR(255),
    escalated BOOLEAN DEFAULT false,
    escalated_at TIMESTAMP,
    handled_by BIGINT REFERENCES users(id),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_tenant_session (tenant_id, session_id)
);
```

**messages**

```sql
CREATE TABLE messages (
    id BIGSERIAL PRIMARY KEY,
    conversation_id BIGINT REFERENCES conversations(id),
    role VARCHAR(20) NOT NULL, -- 'user' or 'assistant'
    content TEXT NOT NULL,
    metadata JSONB,
    created_at TIMESTAMP,
    INDEX idx_conversation (conversation_id)
);
```

**medications**

```sql
CREATE TABLE medications (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT REFERENCES tenants(id),
    name VARCHAR(255) NOT NULL,
    generic_name VARCHAR(255),
    indications TEXT,
    contraindications TEXT,
    side_effects TEXT,
    interactions TEXT,
    dosage_forms TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_tenant_name (tenant_id, name)
);
```

**inventory**

```sql
CREATE TABLE inventory (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT REFERENCES tenants(id),
    medication_id BIGINT REFERENCES medications(id),
    quantity INT NOT NULL DEFAULT 0,
    unit VARCHAR(50),
    price DECIMAL(10,2),
    last_updated TIMESTAMP,
    INDEX idx_tenant_med (tenant_id, medication_id)
);
```

**faq_documents**

```sql
CREATE TABLE faq_documents (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT REFERENCES tenants(id),
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    category VARCHAR(100),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**vector_embeddings** (pgvector)

```sql
CREATE EXTENSION IF NOT EXISTS vector;

CREATE TABLE vector_embeddings (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT REFERENCES tenants(id),
    content TEXT NOT NULL,
    embedding vector(768), -- Gemini embedding dimension
    metadata JSONB,
    source_type VARCHAR(50), -- 'medication', 'faq', 'inventory'
    source_id BIGINT,
    created_at TIMESTAMP,
    INDEX idx_tenant (tenant_id)
);

-- Create vector similarity index
CREATE INDEX ON vector_embeddings
USING ivfflat (embedding vector_cosine_ops)
WITH (lists = 100);
```

### Eloquent Models

**Tenant.php**

```php
class Tenant extends Model
{
    protected $fillable = ['name', 'tenant_token', 'vector_namespace', 'branding'];
    protected $casts = ['branding' => 'array'];

    public function users() { return $this->hasMany(User::class); }
    public function conversations() { return $this->hasMany(Conversation::class); }
    public function medications() { return $this->hasMany(Medication::class); }
}
```

**Conversation.php**

```php
class Conversation extends Model
{
    protected $fillable = ['tenant_id', 'session_id', 'escalated'];

    public function messages() { return $this->hasMany(Message::class); }
    public function tenant() { return $this->belongsTo(Tenant::class); }
}
```

## Correctness Properties

_A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees._

### Property 1: Tenant Creation Uniqueness

_For any_ set of pharmacy sign-ups, creating multiple tenants should result in unique tenant_id and tenant_token values with no collisions.

**Validates: Requirements 1.1, 10.1**

### Property 2: Tenant Data Isolation

_For any_ two different tenants with their own data, querying data for tenant A should never return data belonging to tenant B.

**Validates: Requirements 1.2, 8.5**

### Property 3: Vector Namespace Uniqueness

_For any_ tenant creation, the system should assign a unique vector_namespace that doesn't conflict with existing namespaces.

**Validates: Requirements 1.3**

### Property 4: Token Authentication

_For any_ API request, the system should reject requests with invalid tenant_token and accept requests with valid tenant_token.

**Validates: Requirements 1.5, 3.1, 8.2**

### Property 5: Widget Configuration Acceptance

_For any_ valid PharmaiWidgetConfig object, the widget should successfully load and apply the configuration settings.

**Validates: Requirements 2.2**

### Property 6: Widget API Communication

_For any_ message sent from the widget, the request should include the tenant_token in the authentication header and proper message format.

**Validates: Requirements 2.4**

### Property 7: Message Persistence

_For any_ authenticated chat message, the system should store the message in the database associated with the correct conversation and tenant.

**Validates: Requirements 3.2**

### Property 8: Safety Engine Execution Order

_For any_ incoming message, the Safety Engine should execute before the RAG pipeline and Gemini API call.

**Validates: Requirements 3.3**

### Property 9: RAG Pipeline Trigger

_For any_ message that passes safety checks, the system should execute the RAG pipeline to retrieve context.

**Validates: Requirements 3.4**

### Property 10: Context Inclusion in Prompt

_For any_ message with retrieved context, the final prompt sent to Gemini should include both the context and the user message.

**Validates: Requirements 3.5, 4.4**

### Property 11: Entity Extraction

_For any_ message containing medication names, the system should extract those entities correctly.

**Validates: Requirements 4.1**

### Property 12: Vector Search Tenant Isolation

_For any_ vector search query, the system should only search within the requesting tenant's namespace.

**Validates: Requirements 4.2**

### Property 13: Safety Pattern Detection

_For any_ message containing dosage prescription requests, child/pregnancy queries, controlled substance requests, or diagnosis attempts, the Safety Engine should trigger escalation.

**Validates: Requirements 5.1, 5.2, 5.3, 5.4**

### Property 14: Escalation Response Format

_For any_ message that triggers escalation, the response should include escalation flag set to true and appropriate message.

**Validates: Requirements 5.5**

### Property 15: CSV Upload and Storage

_For any_ valid medication or inventory CSV file uploaded by an admin, the system should parse and store all records with the correct tenant_id.

**Validates: Requirements 6.1, 6.2**

### Property 16: Document Storage with Tenant Association

_For any_ FAQ document uploaded, the system should store it associated with the correct tenant_id.

**Validates: Requirements 6.3**

### Property 17: Async Embedding Queue Dispatch

_For any_ data upload event, the system should dispatch an embedding generation job to the queue without blocking the response.

**Validates: Requirements 6.4, 9.3**

### Property 18: Embedding Storage with Namespace

_For any_ generated embedding, the system should store it in the vector database under the correct tenant's namespace.

**Validates: Requirements 6.5**

### Property 19: Conversation Tenant Filtering

_For any_ admin viewing conversations, the system should only display conversations belonging to their tenant.

**Validates: Requirements 7.2**

### Property 20: Escalation Flagging

_For any_ conversation that triggers escalation, the system should set the escalated flag to true and record the timestamp.

**Validates: Requirements 7.3**

### Property 21: Escalation Handling State

_For any_ admin response to an escalated conversation, the system should save the response and update the conversation state.

**Validates: Requirements 7.4**

### Property 22: PII Encryption

_For any_ sensitive data stored in the database, the system should encrypt PII fields before storage.

**Validates: Requirements 8.3**

### Property 23: Audit Log Creation

_For any_ pharmacist response to a conversation, the system should create an audit log entry with timestamp and user information.

**Validates: Requirements 8.4**

### Property 24: Embed Code Generation

_For any_ newly created tenant, the system should generate an embed code snippet containing the correct tenant_token.

**Validates: Requirements 10.2**

### Property 25: Onboarding Data Indexing

_For any_ tenant uploading initial data, the system should index the data and enable chatbot functionality.

**Validates: Requirements 10.4**

## Error Handling

### API Error Responses

All API endpoints should return consistent error responses:

```json
{
    "error": {
        "code": "INVALID_TOKEN",
        "message": "The provided tenant token is invalid",
        "status": 401
    }
}
```

**Error Codes:**

-   `INVALID_TOKEN` (401) - Authentication failed
-   `TENANT_NOT_FOUND` (404) - Tenant doesn't exist
-   `RATE_LIMIT_EXCEEDED` (429) - Too many requests
-   `ESCALATION_REQUIRED` (200) - Safety trigger, not an error
-   `INTERNAL_ERROR` (500) - Server error
-   `VALIDATION_ERROR` (422) - Invalid input data

### Safety Engine Escalation

When escalation is triggered, return success response with escalation flag:

```json
{
    "response": "I need to connect you with a licensed pharmacist for this question.",
    "escalation": true,
    "escalation_reason": "dosage_prescription_request"
}
```

### Widget Error Handling

The Vue widget should handle:

-   Network failures - Show retry button
-   Invalid configuration - Display setup error
-   API errors - Show user-friendly message
-   Timeout - Retry with exponential backoff

## Testing Strategy

### Unit Testing

**Backend (PHPUnit/Pest):**

-   Tenant Service: tenant creation, token validation, isolation
-   Safety Engine: pattern matching, escalation triggers
-   RAG Pipeline: entity extraction, context building
-   Embedding Service: chunking, vector storage
-   Controllers: request validation, response formatting

**Frontend (Vitest):**

-   Widget configuration parsing
-   Message formatting
-   API client methods
-   UI component rendering

### Property-Based Testing

Use Pest with custom generators to test universal properties:

**Key Properties to Test:**

-   Tenant isolation across random data sets
-   Token authentication with valid/invalid tokens
-   Safety pattern detection across varied inputs
-   Entity extraction with different medication names
-   Vector search namespace isolation

**Configuration:**

-   Minimum 100 iterations per property test
-   Use Pest's `dataset()` for input generation
-   Tag each test with corresponding property number

**Example:**

```php
// Feature: pharmacy-chatbot, Property 2: Tenant Data Isolation
it('never returns data from other tenants', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    Medication::factory()->count(10)->create(['tenant_id' => $tenant1->id]);
    Medication::factory()->count(10)->create(['tenant_id' => $tenant2->id]);

    $tenant1Meds = Medication::where('tenant_id', $tenant1->id)->get();

    expect($tenant1Meds)->each(
        fn ($med) => $med->tenant_id->toBe($tenant1->id)
    );
})->repeat(100);
```

### Integration Testing

-   Full chat flow: widget → API → RAG → Gemini → response
-   Admin dashboard: login → view conversations → respond
-   Onboarding flow: signup → upload data → embed widget
-   Escalation workflow: trigger → flag → admin response

### End-to-End Testing

-   Deploy widget on test pharmacy site
-   Simulate customer interactions
-   Verify responses and escalations
-   Test admin interventions

## Performance Considerations

### Caching Strategy

**Cache Layers:**

1. **Tenant Config** - Cache branding and settings (TTL: 1 hour)
2. **Vector Search Results** - Cache frequent queries (TTL: 15 minutes)
3. **Medication Data** - Cache inventory lookups (TTL: 5 minutes)

**Implementation:**

```php
Cache::remember("tenant:{$tenantId}:config", 3600, function () use ($tenantId) {
    return Tenant::find($tenantId)->branding;
});
```

### Database Optimization

**Indexes:**

-   `tenants(tenant_token)` - Unique index for auth
-   `conversations(tenant_id, session_id)` - Composite for lookups
-   `messages(conversation_id)` - Foreign key index
-   `medications(tenant_id, name)` - Search optimization
-   `vector_embeddings(tenant_id)` - Tenant filtering
-   `vector_embeddings USING ivfflat (embedding)` - Vector similarity

**Query Optimization:**

-   Always include `tenant_id` in WHERE clauses
-   Use eager loading for relationships
-   Limit vector search results (top 5-10)
-   Paginate conversation lists

### API Rate Limiting

```php
// routes/api.php
Route::middleware(['throttle:60,1'])->group(function () {
    Route::post('/tenant/chat', [ChatController::class, 'chat']);
});
```

### Queue Management

**Job Priority:**

1. High: Safety checks, message processing
2. Medium: Embedding generation
3. Low: Analytics, cleanup tasks

**Configuration:**

```php
// config/queue.php
'connections' => [
    'database' => [
        'driver' => 'database',
        'table' => 'jobs',
        'queue' => 'default',
        'retry_after' => 90,
    ],
],
```

## Security Considerations

### Authentication & Authorization

**Tenant Token:**

-   Generate using `Str::random(64)`
-   Store hashed in database
-   Validate on every API request
-   Rotate on security events

**Admin Authentication:**

-   Use Laravel Sanctum for API tokens
-   Implement role-based access control
-   Enforce password complexity
-   Enable 2FA for admin accounts

### Data Protection

**Encryption:**

-   Encrypt PII fields using Laravel's encrypted casts
-   Use HTTPS for all communications
-   Encrypt database backups
-   Secure API keys in environment variables

**Tenant Isolation:**

-   Global scope on all tenant models
-   Server-side validation of tenant_id
-   Separate vector namespaces
-   Audit logs for cross-tenant access attempts

### Input Validation

**Sanitization:**

-   Validate all user inputs
-   Escape HTML in messages
-   Limit message length (max 1000 chars)
-   Validate file uploads (CSV format, size limits)

**SQL Injection Prevention:**

-   Use Eloquent ORM exclusively
-   Parameterized queries for raw SQL
-   Validate tenant_id as integer

### API Security

**Headers:**

```
X-Tenant-Token: {token}
Content-Type: application/json
X-Request-ID: {uuid}
```

**CORS Configuration:**

```php
// config/cors.php
'allowed_origins' => env('CORS_ALLOWED_ORIGINS', '*'),
'allowed_methods' => ['POST', 'GET'],
'allowed_headers' => ['Content-Type', 'X-Tenant-Token'],
```

## Deployment Architecture

### Infrastructure

**Production Stack:**

-   Web Server: Nginx + PHP-FPM
-   Application: Laravel 11 on PHP 8.2
-   Database: PostgreSQL 15 with pgvector
-   Queue: Redis for job processing
-   Cache: Redis for application cache
-   CDN: CloudFlare for widget distribution

**Scaling Strategy:**

-   Horizontal: Multiple Laravel instances behind load balancer
-   Vertical: Scale database and Redis as needed
-   Queue Workers: Auto-scale based on job backlog

### Environment Configuration

**.env Requirements:**

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.yoursaas.com

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_DATABASE=pharmacy_chatbot
DB_USERNAME=app_user
DB_PASSWORD=secure_password

GEMINI_API_KEY=your_gemini_api_key
GEMINI_MODEL=gemini-pro
GEMINI_EMBEDDING_MODEL=text-embedding-004

QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis

WIDGET_CDN_URL=https://cdn.yoursaas.com
```

### Monitoring

**Metrics to Track:**

-   API response times
-   Gemini API latency
-   Queue job processing time
-   Error rates by endpoint
-   Tenant-specific usage
-   Vector search performance

**Tools:**

-   Laravel Telescope (development)
-   Application logs (production)
-   Database query monitoring
-   Uptime monitoring
