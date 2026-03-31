<?php

namespace App\Http\Middleware;

use App\Support\ProofOfWorkService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProofOfWorkMiddleware
{
    public function __construct(private readonly ProofOfWorkService $proofOfWorkService)
    {
    }

    public function handle(Request $request, Closure $next, string $context): Response
    {
        $this->proofOfWorkService->verify($request, $context);

        return $next($request);
    }
}
