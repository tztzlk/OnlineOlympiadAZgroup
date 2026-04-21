<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OlympiadRequest;
use App\Models\PaymentImportRow;
use App\Support\KaspiPaymentReconciler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Handles Kaspi Pay GET-based Check/Pay callbacks.
 * Kaspi docs: https://kaspi.kz/merchantinfo/documentation
 *
 * Both endpoints respond with JSON: {txn_id, result, sum, comment}
 * result codes: 0=success, 1=not found, 2=already paid, 3=server error
 */
class KaspiCallbackController extends Controller
{
    public function __construct(
        protected KaspiPaymentReconciler $reconciler,
    ) {}

    /**
     * GET /api/kaspi/check?txn_id=…&account=REQUEST_PUBLIC_ID&sum=…
     * Kaspi calls this before charging to verify the account exists and amount is correct.
     */
    public function check(Request $request)
    {
        $txnId = (int) $request->query('txn_id', 0);
        $account = (string) $request->query('account', '');
        $sum = (float) $request->query('sum', 0);

        Log::channel('security')->info('kaspi.check', [
            'txn_id' => $txnId,
            'account' => $account,
            'sum' => $sum,
            'ip' => $request->ip(),
        ]);

        $olympiadRequest = $this->findRequest($account);

        if (!$olympiadRequest) {
            return $this->result($txnId, 1, 0, 'Заявка не найдена');
        }

        if ($olympiadRequest->payment_status === 'paid') {
            return $this->result($txnId, 2, $sum, 'Уже оплачено');
        }

        return $this->result($txnId, 0, $sum, 'OK');
    }

    /**
     * GET /api/kaspi/pay?txn_id=…&account=REQUEST_PUBLIC_ID&sum=…&command=pay
     * Kaspi calls this after a successful charge. We must be idempotent.
     */
    public function pay(Request $request)
    {
        $txnId = (int) $request->query('txn_id', 0);
        $account = (string) $request->query('account', '');
        $sum = (float) $request->query('sum', 0);

        Log::channel('security')->info('kaspi.pay', [
            'txn_id' => $txnId,
            'account' => $account,
            'sum' => $sum,
            'ip' => $request->ip(),
        ]);

        $olympiadRequest = $this->findRequest($account);

        if (!$olympiadRequest) {
            return $this->result($txnId, 1, 0, 'Заявка не найдена');
        }

        if ($olympiadRequest->payment_status === 'paid') {
            // Idempotent: already processed, return success
            return $this->result($txnId, 0, $sum, 'Уже оплачено');
        }

        try {
            $this->processPayment($olympiadRequest, $txnId, $sum);
        } catch (\Throwable $e) {
            Log::channel('security')->error('kaspi.pay_error', [
                'txn_id' => $txnId,
                'account' => $account,
                'error' => $e->getMessage(),
            ]);

            return $this->result($txnId, 3, 0, 'Внутренняя ошибка');
        }

        return $this->result($txnId, 0, $sum, 'OK');
    }

    protected function processPayment(OlympiadRequest $olympiadRequest, int $txnId, float $sum): void
    {
        DB::transaction(function () use ($olympiadRequest, $txnId, $sum) {
            // Create a PaymentImportRow so the existing reconciler logic handles DB state,
            // notifications, onboarding sync, etc. via markMatched()
            $importRow = PaymentImportRow::create([
                'provider' => 'kaspi',
                'external_reference' => (string) $txnId,
                'amount' => $sum,
                'comment' => $olympiadRequest->public_id,
                'paid_at' => now(),
                'status' => 'new',
            ]);

            $paymentRecord = $olympiadRequest->paymentRecord
                ?? $this->reconciler->reportPayment($olympiadRequest);

            // Force-match: txn_id in comment guarantees resolveByRequestReference will match
            $this->reconciler->reconcileImportedRows([$importRow]);
        });
    }

    protected function findRequest(string $account): ?OlympiadRequest
    {
        if ($account === '') {
            return null;
        }

        return OlympiadRequest::query()
            ->with(['paymentRecord'])
            ->where('public_id', $account)
            ->first();
    }

    protected function result(int $txnId, int $resultCode, float $sum, string $comment): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'txn_id' => $txnId,
            'result' => $resultCode,
            'sum' => $sum,
            'comment' => $comment,
        ]);
    }
}
