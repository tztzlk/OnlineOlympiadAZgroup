<?php

use App\Support\DeploymentSafetyGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('adds security headers and hsts to responses', function () {
    config()->set('security.headers.csp', "default-src 'self'");

    $this->withServerVariables(['HTTP_X_FORWARDED_PROTO' => 'https'])
        ->get('/up')
        ->assertOk()
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Content-Security-Policy', "default-src 'self'")
        ->assertHeader('Strict-Transport-Security');
});

it('enforces strict cors only for allowed origins', function () {
    config()->set('security.cors.allowed_origins', ['https://app.example.com']);
    config()->set('cors.allowed_origins', ['https://app.example.com']);

    $this->withHeaders([
        'Origin' => 'https://app.example.com',
    ])->getJson('/api/subjects')
        ->assertOk()
        ->assertHeader('Access-Control-Allow-Origin', 'https://app.example.com');

    $this->withHeaders([
        'Origin' => 'https://evil.example.com',
    ])->getJson('/api/subjects')
        ->assertStatus(403);
});

it('redirects http traffic to https when forced', function () {
    config()->set('security.enforce_https', true);
    config()->set('security.allow_testing_https_redirect', true);

    $this->get('/up')
        ->assertRedirect('https://127.0.0.1:8000/up');
});

it('detects unsafe preview deployments wired to production database', function () {
    $guard = new DeploymentSafetyGuard();

    $original = [
        'VERCEL_ENV' => env('VERCEL_ENV'),
        'DATABASE_URL' => env('DATABASE_URL'),
        'PRODUCTION_DATABASE_URL' => env('PRODUCTION_DATABASE_URL'),
        'DB_HOST' => env('DB_HOST'),
        'DB_DATABASE' => env('DB_DATABASE'),
        'PRODUCTION_DB_HOST' => env('PRODUCTION_DB_HOST'),
        'PRODUCTION_DB_DATABASE' => env('PRODUCTION_DB_DATABASE'),
    ];

    putenv('VERCEL_ENV=preview');
    putenv('DATABASE_URL=postgres://prod-db');
    putenv('PRODUCTION_DATABASE_URL=postgres://prod-db');

    expect(fn () => $guard->ensureSafeConfiguration())
        ->toThrow(RuntimeException::class);

    foreach ($original as $key => $value) {
        if ($value === false || $value === null) {
            putenv($key);
            continue;
        }

        putenv($key . '=' . $value);
    }
});
