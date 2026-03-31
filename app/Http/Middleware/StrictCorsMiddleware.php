<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class StrictCorsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $origin = (string) $request->headers->get('Origin', '');

        if ($origin !== '' && !$this->isAllowedOrigin($origin)) {
            Log::channel('security')->warning('cors.origin_blocked', [
                'event' => 'cors.origin_blocked',
                'origin' => $origin,
                'path' => $request->path(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Origin is not allowed.',
            ], 403);
        }

        if ($request->isMethod('OPTIONS')) {
            $response = response()->noContent();

            return $this->addCorsHeaders($response, $origin);
        }

        $response = $next($request);

        return $this->addCorsHeaders($response, $origin);
    }

    protected function addCorsHeaders(Response $response, string $origin): Response
    {
        if ($origin !== '' && $this->isAllowedOrigin($origin)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Allow-Methods', implode(', ', config('security.cors.allowed_methods', ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'])));
            $response->headers->set('Access-Control-Allow-Headers', implode(', ', config('security.cors.allowed_headers', ['Content-Type', 'Authorization', 'X-Requested-With', 'Accept', 'Origin'])));
            $response->headers->set('Vary', trim($response->headers->get('Vary', '') . ', Origin', ', '));
        }

        return $response;
    }

    protected function isAllowedOrigin(string $origin): bool
    {
        return in_array($origin, config('security.cors.allowed_origins', []), true);
    }
}
