<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle($request, Closure $next, ?string $capability = null): Response
    {
        $user = $request->user();

        if (!$user || !$user->hasAdminAccess()) {
            return response()->json([
                'message' => 'Forbidden',
            ], 403);
        }

        if ($capability && !$user->hasAdminCapability($capability)) {
            return response()->json([
                'message' => 'Недостаточно прав для этого раздела.',
            ], 403);
        }

        return $next($request);
    }
}
