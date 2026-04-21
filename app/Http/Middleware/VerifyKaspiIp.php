<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyKaspiIp
{
    // Kaspi Pay official callback IP ranges (update if Kaspi publishes new ones)
    protected array $allowedRanges = [
        '91.185.25.0/24',
        '91.185.26.0/24',
        '91.185.27.0/24',
        '91.185.28.0/24',
        '185.114.196.0/22',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();

        if (!$this->isAllowed($ip)) {
            Log::channel('security')->warning('kaspi.ip_blocked', [
                'ip' => $ip,
                'path' => $request->path(),
            ]);

            return response()->json(['txn_id' => 0, 'result' => 3, 'comment' => 'Forbidden'], 403);
        }

        return $next($request);
    }

    protected function isAllowed(string $ip): bool
    {
        if (app()->isLocal()) {
            return true;
        }

        foreach ($this->allowedRanges as $cidr) {
            if ($this->ipInCidr($ip, $cidr)) {
                return true;
            }
        }

        return false;
    }

    protected function ipInCidr(string $ip, string $cidr): bool
    {
        [$subnet, $bits] = explode('/', $cidr);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);

        if ($ip === false || $subnet === false) {
            return false;
        }

        $mask = ~((1 << (32 - (int) $bits)) - 1);

        return ($ip & $mask) === ($subnet & $mask);
    }
}
