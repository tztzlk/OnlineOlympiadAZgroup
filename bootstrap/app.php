<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
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
            \App\Http\Middleware\ForceUtf8HtmlResponseMiddleware::class,
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
        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => "\u{0422}\u{0440}\u{0435}\u{0431}\u{0443}\u{0435}\u{0442}\u{0441}\u{044F} \u{0430}\u{0432}\u{0442}\u{043E}\u{0440}\u{0438}\u{0437}\u{0430}\u{0446}\u{0438}\u{044F}.",
                ], 401);
            }

            return null;
        });

        // Страховка для PostgreSQL: некорректный идентификатор (не UUID) в запросе — это «не найдено», а не 500.
        $exceptions->render(function (\Illuminate\Database\QueryException $exception, Request $request) {
            if (($exception->errorInfo[0] ?? null) !== '22P02') {
                return null;
            }

            return $request->is('api/*')
                ? response()->json(['message' => 'Не найдено.'], 404)
                : abort(404);
        });

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
