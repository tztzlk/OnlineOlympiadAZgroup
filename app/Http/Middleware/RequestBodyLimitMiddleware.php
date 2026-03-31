<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestBodyLimitMiddleware
{
    public function handle(Request $request, Closure $next, int $limitKb = 1024): Response
    {
        $effectiveLimitKb = $this->resolveEffectiveLimitKb($request, $limitKb);
        $contentLength = (int) $request->server('CONTENT_LENGTH', 0);

        if ($contentLength > ($effectiveLimitKb * 1024)) {
            abort(413, 'Размер запроса превышает допустимый лимит.');
        }

        return $next($request);
    }

    protected function resolveEffectiveLimitKb(Request $request, int $fallbackKb): int
    {
        $route = $request->route();

        if (!$route) {
            return $fallbackKb;
        }

        $limits = collect($route->gatherMiddleware())
            ->filter(fn (string $middleware) => str_starts_with($middleware, 'body.limit:'))
            ->map(function (string $middleware) {
                [, $value] = explode(':', $middleware, 2);

                return (int) $value;
            })
            ->filter(fn (int $value) => $value > 0);

        return $limits->max() ?: $fallbackKb;
    }
}
