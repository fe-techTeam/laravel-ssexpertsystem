<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Base API Gateway URL
    |--------------------------------------------------------------------------
    |
    | The base URL endpoint for SSExpertSystem Gateway API.
    |
    */
    'base_url' => env('SSEXPERT_BASE_URL', 'http://api.ssexpertsystem.com'),

    /*
    |--------------------------------------------------------------------------
    | API Credentials
    |--------------------------------------------------------------------------
    |
    | Your SSExpert account API key and client ID.
    |
    */
    'api_key' => env('SSEXPERT_API_KEY', ''),
    'client_id' => env('SSEXPERT_CLIENT_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | TRAI DLT Principal Entity ID (PEID)
    |--------------------------------------------------------------------------
    |
    | Your registered Principal Entity ID under Telecom DLT regulations.
    |
    */
    'principle_entity_id' => env('SSEXPERT_PEID', ''),

    /*
    |--------------------------------------------------------------------------
    | Default Sender ID (Header)
    |--------------------------------------------------------------------------
    |
    | Default approved 6-character sender ID / header.
    |
    */
    'sender_id' => env('SSEXPERT_SENDER_ID', 'TESTID'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Configuration
    |--------------------------------------------------------------------------
    |
    | Timeout and automated exponential retry settings.
    |
    */
    'timeout' => (int) env('SSEXPERT_TIMEOUT', 15),

    'retry' => [
        'times' => (int) env('SSEXPERT_RETRY_TIMES', 3),
        'sleep' => (int) env('SSEXPERT_RETRY_SLEEP', 100),
    ],

];
