<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append([
            \App\Http\Middleware\EnforceHttpsMiddleware::class,
            \App\Http\Middleware\SecurityHeadersMiddleware::class,
            \App\Http\Middleware\StrictCorsMiddleware::class,
        ]);

        $middleware->api(append: [
            'body.limit:1024',
            \App\Http\Middleware\RequestSecurityMonitoringMiddleware::class,
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'pow' => \App\Http\Middleware\ProofOfWorkMiddleware::class,
            'body.limit' => \App\Http\Middleware\RequestBodyLimitMiddleware::class,
            'verify.webhook' => \App\Http\Middleware\VerifyWebhookSignature::class,
            'ai.quota' => \App\Http\Middleware\AiRateLimitMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (\Throwable $throwable): void {
            if (!app()->bound('request')) {
                return;
            }

            $request = app('request');

            if (!$request || !$request->is('api/*')) {
                return;
            }

            $status = method_exists($throwable, 'getStatusCode') ? $throwable->getStatusCode() : 500;

            Log::channel('security')->error('api.exception', [
                'event' => 'api.exception',
                'status' => $status,
                'method' => $request->method(),
                'path' => '/' . ltrim($request->path(), '/'),
                'ip' => $request->ip(),
                'user_public_id' => $request->user()?->public_id,
                'exception' => $throwable::class,
                'message' => $throwable->getMessage(),
            ]);
        });
    })->create();
