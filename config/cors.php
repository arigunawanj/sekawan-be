<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // IMPORTANT:
    // - Browser requests from the Next.js frontend will often trigger a CORS preflight (OPTIONS)
    //   because we send `Authorization` and `Content-Type: application/json`.
    // - Origins must match exactly (scheme + host + port). Trailing slashes can break matching.
    //
    // Configure production via env:
    // - FRONTEND_URL=https://sekawan.arigunawanj.com
    // OR (multiple origins):
    // - CORS_ALLOWED_ORIGINS=https://sekawan.arigunawanj.com,http://localhost:3000
    'allowed_origins' => (function (): array {
        $raw = env('CORS_ALLOWED_ORIGINS', env('FRONTEND_URL', 'http://localhost:3000'));
        $origins = array_map('trim', explode(',', (string) $raw));
        $origins = array_map(static fn (string $o): string => rtrim($o, '/'), $origins);

        // safe defaults (dev + production)
        $origins[] = 'https://sekawan.arigunawanj.com';
        $origins[] = 'http://localhost:3000';
        $origins[] = 'http://127.0.0.1:3000';

        return array_values(array_unique(array_filter($origins)));
    })(),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];


