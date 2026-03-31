<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceHttpsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldRedirectToHttps($request)) {
            return redirect()->secure($request->getRequestUri(), 301);
        }

        return $next($request);
    }

    protected function shouldRedirectToHttps(Request $request): bool
    {
        if (!config('security.enforce_https', false)) {
            return false;
        }

        if (app()->environment(['local', 'testing']) && !config('security.allow_testing_https_redirect', false)) {
            return false;
        }

        if ($request->secure()) {
            return false;
        }

        return strtolower((string) $request->header('x-forwarded-proto')) !== 'https';
    }
}
