<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProofOfWorkService
{
    public function issueChallenge(string $context, Request $request): array
    {
        $difficulty = (int) config('security.pow.difficulty', 3);
        $ttl = (int) config('security.pow.ttl_seconds', 900);
        $payload = [
            'context' => $context,
            'salt' => bin2hex(random_bytes(16)),
            'difficulty' => $difficulty,
            'expires_at' => now()->addSeconds($ttl)->timestamp,
            'ip_hash' => hash('sha256', (string) $request->ip()),
        ];

        $encodedPayload = $this->base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR));
        $signature = hash_hmac('sha256', $encodedPayload, $this->secret());

        return [
            'token' => $encodedPayload . '.' . $signature,
            'difficulty' => $difficulty,
            'expires_at' => $payload['expires_at'],
            'algorithm' => 'sha256',
        ];
    }

    public function verify(Request $request, string $context): void
    {
        $token = (string) $request->input('pow_token', '');
        $nonce = (string) $request->input('pow_nonce', '');

        abort_if($token === '' || $nonce === '', 422, 'Не пройдена anti-bot проверка.');

        [$encodedPayload, $signature] = array_pad(explode('.', $token, 2), 2, null);

        abort_unless($encodedPayload && $signature, 422, 'Некорректный anti-bot токен.');
        abort_unless(hash_equals(hash_hmac('sha256', $encodedPayload, $this->secret()), $signature), 422, 'Некорректная подпись anti-bot токена.');

        $payload = json_decode($this->base64UrlDecode($encodedPayload), true, 512, JSON_THROW_ON_ERROR);

        abort_unless(($payload['context'] ?? null) === $context, 422, 'Токен anti-bot выдан для другой формы.');
        abort_unless(($payload['expires_at'] ?? 0) >= now()->timestamp, 422, 'Срок действия anti-bot токена истёк.');
        abort_unless(($payload['ip_hash'] ?? '') === hash('sha256', (string) $request->ip()), 422, 'Токен anti-bot не подходит для этого IP.');
        abort_unless(strlen($nonce) <= 255, 422, 'Некорректный anti-bot nonce.');

        $prefix = str_repeat('0', (int) ($payload['difficulty'] ?? config('security.pow.difficulty', 3)));
        $hash = hash('sha256', $token . '|' . $nonce);

        abort_unless(str_starts_with($hash, $prefix), 422, 'Anti-bot проверка не пройдена.');

        $cacheKey = 'pow:' . hash('sha256', $token . '|' . $nonce);
        $used = false;

        Cache::lock('pow_lock:' . $cacheKey, 5)->get(function () use ($cacheKey, &$used) {
            if (Cache::has($cacheKey)) {
                $used = true;

                return;
            }
            Cache::put($cacheKey, true, now()->addMinutes(15));
        });

        abort_if($used, 422, 'Этот anti-bot токен уже использован.');
    }

    protected function secret(): string
    {
        return (string) config('app.key');
    }

    protected function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    protected function base64UrlDecode(string $value): string
    {
        $padding = strlen($value) % 4;

        if ($padding > 0) {
            $value .= str_repeat('=', 4 - $padding);
        }

        return base64_decode(strtr($value, '-_', '+/')) ?: '';
    }
}
