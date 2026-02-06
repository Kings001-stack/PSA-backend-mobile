# Implementation Plan

-   [x] 1. Set up project foundation and database

    -   Configure PostgreSQL with pgvector extension
    -   Set up environment variables for Gemini API
    -   Configure Laravel queues and cache
    -   _Requirements: 1.1, 8.1_

-   [ ] 2. Implement multi-tenant data models and migrations

    -   Create tenants table with tenant_token and vector_namespace
    -   Create users table with tenant relationship

    -   Create conversations and messages tables
    -   Create medications, inventory, and faq_documents tables
    -   Create vector_embeddings table with pgvector
    -   Add indexes for performance optimization
    -   _Requirements: 1.1, 1.2, 1.3_

-   [ ]\* 2.1 Write property test for tenant creation uniqueness

    -   **Property 1: Tenant Creation Uniqueness**
    -   **Validates: Requirements 1.1, 10.1**

-   [ ]\* 2.2 Write property test for vector namespace uniqueness

    -   **Property 3: Vector Namespace Uniqueness**
    -   **Validates: Requirements 1.3**

-   [x] 3. Create Eloquent models with tenant isolation

    -   Implement Tenant model with relationships

    -   Implement User, Conversation, Message models
    -   Implement Medication, Inventory, FaqDocument models
    -   Implement VectorEmbedding model
    -   Add global scopes for tenant isolation
    -   _Requirements: 1.2, 8.5_

-   [ ]\* 3.1 Write property test for tenant data isolation

    -   **Property 2: Tenant Data Isolation**
    -   **Validates: Requirements 1.2, 8.5**

-   [x] 4. Implement Tenant Service

    -   Create TenantService class for tenant management
    -   Implement createTenant() method with unique token generation
    -   Implement validateToken() method for authentication
    -   Implement getTenantContext() method
    -   Add tenant provisioning logic
    -   _Requirements: 1.1, 1.5, 10.1_

-   [ ]\* 4.1 Write property test for token authentication

    -   **Property 4: Token Authentication**
    -   **Validates: Requirements 1.5, 3.1, 8.2**

-   [x] 5. Implement Safety Engine

    -   Create SafetyEngine class
    -   Define escalation patterns (dosage, pregnancy, controlled substances, diagnosis)
    -   Implement check() method with pattern matching
    -   Add escalation response formatting
    -   _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5_

-   [ ]\* 5.1 Write property test for safety pattern detection

    -   **Property 13: Safety Pattern Detection**
    -   **Validates: Requirements 5.1, 5.2, 5.3, 5.4**

-   [ ]\* 5.2 Write property test for escalation response format

    -   **Property 14: Escalation Response Format**
    -   **Validates: Requirements 5.5**

-   [x] 6. Implement Gemini API integration

    -   Create GeminiService class
    -   Implement generateResponse() method for chat
    -   Implement generateEmbedding() method for vectors
    -   Add error handling and retry logic
    -   Configure API client with timeout settings
    -   _Requirements: 3.5, 4.5_

-   [x] 7. Implement RAG Pipeline Service

    -   Create RagPipelineService class
    -   Implement extractEntities() for medication name extraction
    -   Implement vectorSearch() for pgvector queries with tenant namespace
    -   Implement buildContext() for structured context formatting
    -   Implement composePrompt() for final prompt assembly
    -   _Requirements: 4.1, 4.2, 4.3, 4.4_

-   [ ]\* 7.1 Write property test for entity extraction

    -   **Property 11: Entity Extraction**
    -   **Validates: Requirements 4.1**

-   [ ]\* 7.2 Write property test for vector search tenant isolation

    -   **Property 12: Vector Search Tenant Isolation**
    -   **Validates: Requirements 4.2**

-   [ ]\* 7.3 Write property test for context inclusion in prompt

    -   **Property 10: Context Inclusion in Prompt**
    -   **Validates: Requirements 3.5, 4.4**

-   [x] 8. Implement Chat API Controller

    -   Create ChatController with chat() method
    -   Add tenant authentication middleware
    -   Implement request validation
    -   Integrate Safety Engine check
    -   Integrate RAG Pipeline
    -   Call Gemini API with context
    -   Store conversation and messages
    -   Return formatted response
    -   _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_

-   [ ]\* 8.1 Write property test for safety engine execution order

    -   **Property 8: Safety Engine Execution Order**
    -   **Validates: Requirements 3.3**

-   [ ]\* 8.2 Write property test for message persistence

    -   **Property 7: Message Persistence**
    -   **Validates: Requirements 3.2**

-   [ ]\* 8.3 Write property test for RAG pipeline trigger

    -   **Property 9: RAG Pipeline Trigger**
    -   **Validates: Requirements 3.4**

-   [x] 9. Implement Embedding Service and Queue Jobs

    -   Create EmbeddingService class
    -   Create GenerateEmbeddingsJob for async processing
    -   Implement document chunking logic
    -   Implement embedding generation and storage
    -   Add job to queue on data upload
    -   _Requirements: 6.4, 6.5, 9.3_

-   [ ]\* 9.1 Write property test for async embedding queue dispatch

    -   **Property 17: Async Embedding Queue Dispatch**
    -   **Validates: Requirements 6.4, 9.3**

-   [ ]\* 9.2 Write property test for embedding storage with namespace

    -   **Property 18: Embedding Storage with Namespace**
    -   **Validates: Requirements 6.5**

-   [x] 10. Implement Admin Dashboard API

    -   Create AdminAuthController for login
    -   Create AdminConversationController for viewing conversations
    -   Create AdminUploadController for CSV/document uploads
    -   Implement conversation filtering by tenant
    -   Implement escalation response handling
    -   Add audit logging for admin actions
    -   _Requirements: 7.1, 7.2, 7.3, 7.4, 8.4_

-   [ ]\* 10.1 Write property test for conversation tenant filtering

    -   **Property 19: Conversation Tenant Filtering**
    -   **Validates: Requirements 7.2**

-   [ ]\* 10.2 Write property test for escalation flagging

    -   **Property 20: Escalation Flagging**
    -   **Validates: Requirements 7.3**

-   [ ]\* 10.3 Write property test for audit log creation

    -   **Property 23: Audit Log Creation**
    -   **Validates: Requirements 8.4**

-   [x] 11. Implement data upload and parsing

    -   Create CSV parser for medications
    -   Create CSV parser for inventory
    -   Implement document parser for FAQs
    -   Add validation for uploaded files
    -   Trigger embedding generation on upload
    -   _Requirements: 6.1, 6.2, 6.3_

-   [ ]\* 11.1 Write property test for CSV upload and storage

    -   **Property 15: CSV Upload and Storage**
    -   **Validates: Requirements 6.1, 6.2**

-   [ ]\* 11.2 Write property test for document storage with tenant association

    -   **Property 16: Document Storage with Tenant Association**
    -   **Validates: Requirements 6.3**

-   [x] 12. Implement security features

    -   Add PII encryption using Laravel encrypted casts
    -   Create audit log model and logging service
    -   Implement rate limiting middleware
    -   Add CORS configuration
    -   Create tenant token validation middleware
    -   _Requirements: 8.2, 8.3, 8.4_

-   [ ]\* 12.1 Write property test for PII encryption

    -   **Property 22: PII Encryption**
    -   **Validates: Requirements 8.3**

-   [ ] 13. Checkpoint - Ensure all backend tests pass

    -   Ensure all tests pass, ask the user if questions arise.

-   [ ] 14. Build Vue.js Chat Widget

    -   Set up Vue 3 project with Vite
    -   Create ChatWidget.vue main component
    -   Create MessageList.vue for displaying messages
    -   Create MessageInput.vue for user input
    -   Create TypingIndicator.vue for loading state
    -   Create EscalationBanner.vue for escalation notifications
    -   Implement API client for backend communication
    -   Add configuration parsing from window.PharmaiWidgetConfig
    -   Style with Tailwind CSS
    -   _Requirements: 2.1, 2.2, 2.3, 2.4_

-   [ ]\* 14.1 Write property test for widget configuration acceptance

    -   **Property 5: Widget Configuration Acceptance**
    -   **Validates: Requirements 2.2**

-   [ ]\* 14.2 Write property test for widget API communication

    -   **Property 6: Widget API Communication**
    -   **Validates: Requirements 2.4**

-   [ ] 15. Build and optimize widget bundle

    -   Configure Vite for production build
    -   Optimize bundle size (target < 80 KB)
    -   Add source maps for debugging
    -   Set up CDN hosting configuration
    -   Create embed code generator
    -   _Requirements: 2.1, 9.1, 10.2_

-   [ ]\* 15.1 Write property test for embed code generation

    -   **Property 24: Embed Code Generation**
    -   **Validates: Requirements 10.2**

-   [ ] 16. Implement tenant onboarding flow

    -   Create tenant registration API endpoint
    -   Generate embed code snippet with tenant_token
    -   Create onboarding instructions page
    -   Implement initial data upload workflow
    -   Enable chatbot after data indexing
    -   _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5_

-   [ ]\* 16.1 Write property test for onboarding data indexing

    -   **Property 25: Onboarding Data Indexing**
    -   **Validates: Requirements 10.4**

-   [ ] 17. Add caching layer

    -   Implement tenant config caching
    -   Add vector search result caching
    -   Cache medication data lookups
    -   Configure cache TTLs
    -   _Requirements: 9.2_

-   [x] 18. Create database seeders for testing

    -   Create TenantSeeder with sample pharmacies
    -   Create MedicationSeeder with common medications
    -   Create InventorySeeder with stock data
    -   Create FaqSeeder with sample FAQs
    -   Create UserSeeder for admin accounts
    -   _Requirements: All_

-   [ ] 19. Final Checkpoint - Full system integration test
    -   Ensure all tests pass, ask the user if questions arise.
