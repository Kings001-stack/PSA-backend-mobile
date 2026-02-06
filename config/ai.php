<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Provider Configuration
    |--------------------------------------------------------------------------
    |
    | This file configures the AI provider used for chat responses.
    | Supported providers: "gemini", "kimi"
    |
    */

    'provider' => env('AI_PROVIDER', 'gemini'),

    /*
    |--------------------------------------------------------------------------
    | Kimi K2 Configuration
    |--------------------------------------------------------------------------
    |
    | Kimi K2 uses an OpenAI-compatible API format.
    | Get your API key from: https://platform.moonshot.cn/
    |
    */

    'kimi' => [
        'api_key' => env('KIMI_API_KEY'),
        'base_url' => env('KIMI_BASE_URL', 'https://api.moonshot.ai/v1'),
        'model' => env('KIMI_MODEL', 'kimi-k2-turbo-preview'),
        'temperature' => env('KIMI_TEMPERATURE', 0.7),
        'timeout' => env('KIMI_TIMEOUT', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenRouter Configuration
    |--------------------------------------------------------------------------
    |
    */

    'openrouter' => [
        'api_key' => env('OPENROUTER_API_KEY'),
        'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),
        'model' => env('OPENROUTER_MODEL', 'nvidia/nemotron-3-nano-30b-a3b:free'),
        'temperature' => env('OPENROUTER_TEMPERATURE', 0.7),
        'timeout' => env('OPENROUTER_TIMEOUT', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Gemini Configuration (Reference to existing config)
    |--------------------------------------------------------------------------
    |
    | Gemini settings are in config/gemini.php for backwards compatibility.
    |
    */

];
