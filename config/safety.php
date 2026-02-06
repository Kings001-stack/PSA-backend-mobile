<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Safety Engine Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the safety engine that detects harmful medical
    | queries and triggers escalations to human pharmacists.
    |
    */

    'enabled' => env('SAFETY_ENGINE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Escalation Settings
    |--------------------------------------------------------------------------
    |
    | Settings for how escalations are handled.
    |
    */

    'escalation' => [
        'auto_flag' => true,
        'notify_pharmacist' => true,
        'log_all_checks' => env('SAFETY_LOG_ALL_CHECKS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Patterns
    |--------------------------------------------------------------------------
    |
    | Additional safety patterns that can be configured per deployment.
    |
    */

    'custom_patterns' => [
        // Add custom regex patterns here
    ],

];
