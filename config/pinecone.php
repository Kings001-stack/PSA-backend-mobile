<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pinecone API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Pinecone vector database integration.
    | Sign up at https://www.pinecone.io/ for a free account.
    |
    */

    'api_key' => env('PINECONE_API_KEY'),

    'environment' => env('PINECONE_ENVIRONMENT'),

    'index_name' => env('PINECONE_INDEX_NAME', 'pharmacy-chatbot'),

    /*
    |--------------------------------------------------------------------------
    | Vector Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for vector dimensions and search parameters.
    |
    */

    'dimensions' => env('VECTOR_DIMENSIONS', 768),

    'metric' => 'cosine',

    'search_limit' => env('VECTOR_SEARCH_LIMIT', 5),

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | HTTP client configuration for Pinecone API requests.
    |
    */

    'timeout' => 30,

    'retry_attempts' => 3,

];
