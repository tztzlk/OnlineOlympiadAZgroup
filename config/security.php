<?php

return [
    'enforce_https' => (bool) env('SECURITY_ENFORCE_HTTPS', true),
    'allow_testing_https_redirect' => (bool) env('SECURITY_ALLOW_TESTING_HTTPS_REDIRECT', false),

    'hsts' => [
        'max_age' => (int) env('SECURITY_HSTS_MAX_AGE', 31536000),
        'preload' => (bool) env('SECURITY_HSTS_PRELOAD', true),
    ],

    'headers' => [
        'x_frame_options' => env('SECURITY_X_FRAME_OPTIONS', 'DENY'),
        'referrer_policy' => env('SECURITY_REFERRER_POLICY', 'strict-origin-when-cross-origin'),
        'permissions_policy' => env('SECURITY_PERMISSIONS_POLICY', 'camera=(), microphone=(), geolocation=()'),
        'csp' => env(
            'SECURITY_CSP',
            "default-src 'self'; base-uri 'self'; frame-ancestors 'none'; form-action 'self' https://kaspi.kz; object-src 'none'; script-src 'self' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; img-src 'self' data: blob: https:; font-src 'self' data: https://cdn.jsdelivr.net https://fonts.gstatic.com; connect-src 'self' https: wss:;"
        ),
    ],

    'webhooks' => [
        'stripe_tolerance_seconds' => (int) env('SECURITY_STRIPE_WEBHOOK_TOLERANCE', 300),
    ],

    'cors' => [
        'allowed_origins' => array_values(array_filter(array_map('trim', explode(',', (string) env('CORS_ALLOWED_ORIGINS', env('FRONTEND_URL', env('APP_URL', ''))))))),
        'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With', 'Accept', 'Origin'],
    ],

    'pow' => [
        'difficulty' => env('POW_DIFFICULTY', 3),
        'ttl_seconds' => env('POW_TTL_SECONDS', 900),
    ],

    'monitoring' => [
        'denied_responses_per_minute' => (int) env('SECURITY_DENIED_THRESHOLD_PER_MINUTE', 10),
        'ip_requests_per_minute' => (int) env('SECURITY_IP_REQUEST_THRESHOLD_PER_MINUTE', 300),
        'global_requests_per_minute' => (int) env('SECURITY_GLOBAL_REQUEST_THRESHOLD_PER_MINUTE', 2000),
    ],
];
