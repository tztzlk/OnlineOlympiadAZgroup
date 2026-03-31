<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', config('security.headers.x_frame_options', 'DENY'));
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', config('security.headers.referrer_policy', 'strict-origin-when-cross-origin'));
        $response->headers->set('Permissions-Policy', config('security.headers.permissions_policy', 'camera=(), microphone=(), geolocation=()'));
        $response->headers->set('Content-Security-Policy', config('security.headers.csp'));

        if ($request->secure() || strtolower((string) $request->header('x-forwarded-proto')) === 'https') {
            $response->headers->set(
                'Strict-Transport-Security',
                sprintf(
                    'max-age=%d; includeSubDomains%s',
                    (int) config('security.hsts.max_age', 31536000),
                    config('security.hsts.preload', true) ? '; preload' : ''
                )
            );
        }

        return $response;
    }
}
