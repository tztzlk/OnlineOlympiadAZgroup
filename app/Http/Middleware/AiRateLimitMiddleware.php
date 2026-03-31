<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class AiRateLimitMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        abort_unless($user, 401, 'Требуется авторизация.');

        $plan = $user->plan ?? 'free';
        $maxAttempts = $plan === 'pro' ? 50 : 5;
        $key = sprintf('ai:%s:%s:%s', $plan, $user->public_id ?? $user->id, now()->toDateString());

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            abort(429, 'Дневной лимит AI-запросов исчерпан для вашего тарифа.');
        }

        $decaySeconds = max(60, now()->diffInSeconds(now()->copy()->endOfDay()));

        RateLimiter::hit($key, $decaySeconds);

        $response = $next($request);
        $response->headers->set('X-AI-Limit', (string) $maxAttempts);
        $response->headers->set('X-AI-Remaining', (string) max(0, $maxAttempts - RateLimiter::attempts($key)));

        return $response;
    }
}
