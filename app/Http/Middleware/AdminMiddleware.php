<?php

namespace App\Http\Middleware;

use Closure;

class AdminMiddleware
{
   public function handle($request, Closure $next)
{
    $user = $request->user();

    if (!$user || !$user->is_admin) {
        return response()->json([
            'message' => 'Forbidden'
        ], 403);
    }

    return $next($request);
}
}