<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OlympiadRequest;
use App\Models\PaymentRecord;
use App\Models\ProcessedWebhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebhookController extends Controller
{
    public function yookassa(Request $request): JsonResponse
    {
        return $this->handleWebhook($request, 'yookassa');
    }

    public function stripe(Request $request): JsonResponse
    {
        return $this->handleWebhook($request, 'stripe');
    }

    public function telegram(Request $request): JsonResponse
    {
        return $this->handleWebhook($request, 'telegram');
    }

    protected function handleWebhook(Request $request, string $provider): JsonResponse
    {
        $payload = $request->json()->all();
        $normalized = $this->normalizeWebhook($provider, $payload);

        $processed = DB::transaction(function () use ($normalized, $payload) {
            $webhook = ProcessedWebhook::query()->firstOrCreate(
                [
                    'provider' => $normalized['provider'],
                    'event_id' => $normalized['event_id'],
                ],
                [
                    'event_type' => $normalized['event_type'],
                    'payload_hash' => hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: ''),
                    'status' => 'received',
                    'received_at' => now(),
                ]
            );

            if ($webhook->processed_at) {
                return [$webhook, true, false];
            }

            [$requestRecord, $paymentRecord] = $this->applyPaymentUpdate($normalized);

            $webhook->forceFill([
                'status' => $normalized['payment_status'] ? 'processed' : 'ignored',
                'olympiad_request_id' => $requestRecord?->id,
                'payment_record_id' => $paymentRecord?->id,
                'processed_at' => now(),
            ])->save();

            return [$webhook, false, (bool) $requestRecord || (bool) $paymentRecord];
        });

        [$webhook, $duplicate, $applied] = $processed;

        return response()->json([
            'received' => true,
            'provider' => $provider,
            'duplicate' => $duplicate,
            'applied' => $applied,
            'event_id' => $webhook->event_id,
        ]);
    }

    protected function applyPaymentUpdate(array $normalized): array
    {
        if (!$normalized['payment_status']) {
            return [null, null];
        }

        $requestRecord = $this->resolveOlympiadRequest($normalized);
        $paymentRecord = $this->resolvePaymentRecord($normalized, $requestRecord);

        if (!$requestRecord && !$paymentRecord) {
            return [null, null];
        }

        $paidAt = $normalized['payment_status'] === 'paid' ? now() : null;

        if ($requestRecord) {
            $requestRecord->forceFill([
                'payment_status' => $normalized['payment_status'],
                'paid_at' => $paidAt,
            ])->save();
        }

        if ($paymentRecord) {
            $paymentRecord->forceFill([
                'status' => $normalized['payment_status'],
                'external_reference' => $normalized['external_reference'],
                'comment' => $normalized['event_type'],
                'paid_at' => $paidAt,
            ])->save();
        }

        return [$requestRecord, $paymentRecord];
    }

    protected function resolveOlympiadRequest(array $normalized): ?OlympiadRequest
    {
        $requestPublicId = $normalized['metadata']['olympiad_request_public_id']
            ?? $normalized['metadata']['olympiad_request_id']
            ?? null;

        if ($requestPublicId) {
            return OlympiadRequest::query()
                ->where('public_id', $requestPublicId)
                ->first();
        }

        $paymentRecord = $this->resolvePaymentRecord($normalized);

        return $paymentRecord?->olympiadRequest;
    }

    protected function resolvePaymentRecord(array $normalized, ?OlympiadRequest $requestRecord = null): ?PaymentRecord
    {
        $paymentPublicId = $normalized['metadata']['payment_record_public_id']
            ?? $normalized['metadata']['payment_record_id']
            ?? null;

        if ($paymentPublicId) {
            return PaymentRecord::query()
                ->where('public_id', $paymentPublicId)
                ->first();
        }

        if ($normalized['external_reference']) {
            $paymentByReference = PaymentRecord::query()
                ->where('external_reference', $normalized['external_reference'])
                ->first();

            if ($paymentByReference) {
                return $paymentByReference;
            }
        }

        if ($requestRecord) {
            return PaymentRecord::query()
                ->where('olympiad_request_id', $requestRecord->id)
                ->first();
        }

        return null;
    }

    protected function normalizeWebhook(string $provider, array $payload): array
    {
        return match ($provider) {
            'stripe' => $this->normalizeStripeWebhook($payload),
            'yookassa' => $this->normalizeYookassaWebhook($payload),
            'telegram' => $this->normalizeTelegramWebhook($payload),
            default => [
                'provider' => $provider,
                'event_id' => hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: ''),
                'event_type' => null,
                'payment_status' => null,
                'external_reference' => null,
                'metadata' => [],
            ],
        };
    }

    protected function normalizeStripeWebhook(array $payload): array
    {
        $eventType = data_get($payload, 'type');
        $object = data_get($payload, 'data.object', []);

        return [
            'provider' => 'stripe',
            'event_id' => (string) ($payload['id'] ?? hash('sha256', json_encode($payload) ?: '')),
            'event_type' => $eventType,
            'payment_status' => match ($eventType) {
                'payment_intent.succeeded', 'checkout.session.completed', 'charge.succeeded', 'invoice.paid' => 'paid',
                'payment_intent.payment_failed', 'charge.failed', 'invoice.payment_failed' => 'failed',
                default => null,
            },
            'external_reference' => data_get($object, 'payment_intent')
                ?? data_get($object, 'id'),
            'metadata' => (array) data_get($object, 'metadata', []),
        ];
    }

    protected function normalizeYookassaWebhook(array $payload): array
    {
        $eventType = data_get($payload, 'event');
        $object = data_get($payload, 'object', []);

        return [
            'provider' => 'yookassa',
            'event_id' => (string) ($payload['id'] ?? ($object['id'] ?? hash('sha256', json_encode($payload) ?: '')) . ':' . ($eventType ?? 'unknown')),
            'event_type' => $eventType,
            'payment_status' => match ($eventType) {
                'payment.succeeded' => 'paid',
                'payment.canceled' => 'failed',
                default => null,
            },
            'external_reference' => data_get($object, 'id'),
            'metadata' => (array) data_get($object, 'metadata', []),
        ];
    }

    protected function normalizeTelegramWebhook(array $payload): array
    {
        return [
            'provider' => 'telegram',
            'event_id' => (string) ($payload['update_id'] ?? hash('sha256', json_encode($payload) ?: '')),
            'event_type' => 'telegram.update',
            'payment_status' => null,
            'external_reference' => null,
            'metadata' => [],
        ];
    }
}
