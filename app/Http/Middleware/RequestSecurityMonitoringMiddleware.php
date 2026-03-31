<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RequestSecurityMonitoringMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = microtime(true);
        $response = $next($request);

        if ($request->is('api/*')) {
            $this->logApiResponse($request, $response, $startedAt);
            $this->monitorDeniedResponses($request, $response);
            $this->monitorRequestSpikes($request);
        }

        return $response;
    }

    protected function logApiResponse(Request $request, Response $response, float $startedAt): void
    {
        $status = $response->getStatusCode();

        if ($status < 400) {
            return;
        }

        $context = $this->baseContext($request, $response, $startedAt);

        if ($status >= 500) {
            Log::channel('security')->error('api.server_error', $context);

            return;
        }

        Log::channel('security')->warning('api.client_error', $context);
    }

    protected function monitorDeniedResponses(Request $request, Response $response): void
    {
        $status = $response->getStatusCode();

        if (!in_array($status, [401, 403], true)) {
            return;
        }

        $bucket = now()->format('YmdHi');
        $ip = (string) $request->ip();
        $counterKey = "security:denied:{$ip}:{$bucket}";
        $count = $this->incrementCounter($counterKey, 120);
        $threshold = (int) config('security.monitoring.denied_responses_per_minute', 10);

        if ($count >= $threshold && Cache::add("security:denied:alerted:{$ip}:{$bucket}", true, now()->addMinutes(2))) {
            Log::channel('anomaly')->warning('security.repeated_denied_responses', [
                'event' => 'security.repeated_denied_responses',
                'ip' => $ip,
                'count' => $count,
                'threshold' => $threshold,
                'path' => $request->path(),
            ]);
        }
    }

    protected function monitorRequestSpikes(Request $request): void
    {
        $bucket = now()->format('YmdHi');
        $globalKey = "security:traffic:global:{$bucket}";
        $ipKey = 'security:traffic:ip:' . $request->ip() . ':' . $bucket;

        $globalCount = $this->incrementCounter($globalKey, 120);
        $ipCount = $this->incrementCounter($ipKey, 120);

        $globalThreshold = (int) config('security.monitoring.global_requests_per_minute', 2000);
        $ipThreshold = (int) config('security.monitoring.ip_requests_per_minute', 300);

        if ($globalCount >= $globalThreshold && Cache::add("security:traffic:global:alerted:{$bucket}", true, now()->addMinutes(2))) {
            Log::channel('anomaly')->warning('traffic.global_spike', [
                'event' => 'traffic.global_spike',
                'count' => $globalCount,
                'threshold' => $globalThreshold,
                'bucket' => $bucket,
            ]);
        }

        if ($ipCount >= $ipThreshold && Cache::add("security:traffic:ip:alerted:{$request->ip()}:{$bucket}", true, now()->addMinutes(2))) {
            Log::channel('anomaly')->warning('traffic.ip_spike', [
                'event' => 'traffic.ip_spike',
                'ip' => $request->ip(),
                'count' => $ipCount,
                'threshold' => $ipThreshold,
                'bucket' => $bucket,
            ]);
        }
    }

    protected function incrementCounter(string $key, int $ttlSeconds): int
    {
        if (Cache::add($key, 0, now()->addSeconds($ttlSeconds))) {
            Cache::put($key, 1, now()->addSeconds($ttlSeconds));

            return 1;
        }

        return (int) Cache::increment($key);
    }

    protected function baseContext(Request $request, Response $response, float $startedAt): array
    {
        return [
            'event' => 'api.response',
            'status' => $response->getStatusCode(),
            'method' => $request->method(),
            'path' => '/' . ltrim($request->path(), '/'),
            'route' => $request->route()?->uri(),
            'ip' => $request->ip(),
            'user_public_id' => $request->user()?->public_id,
            'user_agent' => $request->userAgent(),
            'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
        ];
    }
}
