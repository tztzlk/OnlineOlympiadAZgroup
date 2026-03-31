<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyWebhookSignature
{
    public function handle(Request $request, Closure $next, string $provider): Response
    {
        $content = (string) $request->getContent();

        $verified = match ($provider) {
            'yookassa' => $this->verifyHmacSignature(
                $request->header('X-Yookassa-Signature'),
                $content,
                (string) config('services.webhooks.yookassa_secret')
            ),
            'stripe' => $this->verifyStripeSignature(
                $request->header('Stripe-Signature'),
                $content,
                (string) config('services.webhooks.stripe_secret')
            ),
            'telegram' => hash_equals(
                (string) config('services.webhooks.telegram_secret_token'),
                (string) $request->header('X-Telegram-Bot-Api-Secret-Token')
            ),
            default => false,
        };

        abort_unless($verified, 401, 'Подпись вебхука не прошла проверку.');

        return $next($request);
    }

    protected function verifyHmacSignature(?string $headerSignature, string $content, string $secret): bool
    {
        if (!$headerSignature || $secret === '') {
            return false;
        }

        $calculated = hash_hmac('sha256', $content, $secret);

        return hash_equals($calculated, $headerSignature);
    }

    protected function verifyStripeSignature(?string $header, string $content, string $secret): bool
    {
        if (!$header || $secret === '') {
            return false;
        }

        $parts = collect(explode(',', $header))
            ->mapWithKeys(function (string $chunk) {
                [$key, $value] = array_pad(explode('=', trim($chunk), 2), 2, null);

                return [$key => $value];
            });

        $timestamp = $parts->get('t');
        $signature = $parts->get('v1');

        if (!$timestamp || !$signature) {
            return false;
        }

        if (abs(time() - (int) $timestamp) > (int) config('security.webhooks.stripe_tolerance_seconds', 300)) {
            return false;
        }

        $signedPayload = $timestamp . '.' . $content;
        $expected = hash_hmac('sha256', $signedPayload, $secret);

        return hash_equals($expected, $signature);
    }
}
