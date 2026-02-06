<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Gemini API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Google Gemini API integration including API keys,
    | model selection, and request parameters.
    |
    */

    'api_key' => env('GEMINI_API_KEY'),

    'model' => env('GEMINI_MODEL', 'gemini-2.5-flash-lite'),

    'embedding_model' => env('GEMINI_EMBEDDING_MODEL', 'text-embedding-004'),

    'max_tokens' => env('GEMINI_MAX_TOKENS', 2048),

    'temperature' => env('GEMINI_TEMPERATURE', 0.7),

    'timeout' => env('GEMINI_TIMEOUT', 30),

    'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),

    /*
    |--------------------------------------------------------------------------
    | Vector Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for vector embeddings and similarity search.
    |
    */

    'vector' => [
        'dimensions' => env('VECTOR_DIMENSIONS', 768),
        'search_limit' => env('VECTOR_SEARCH_LIMIT', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Rate limits for API requests to prevent quota exhaustion.
    |
    */

    'rate_limit' => [
        'per_minute' => env('RATE_LIMIT_PER_MINUTE', 60),
        'per_hour' => env('RATE_LIMIT_PER_HOUR', 1000),
    ],

];
