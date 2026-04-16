<?php

namespace App\Support;

use App\Models\OlympiadRequest;
use App\Models\PaymentImportRow;
use App\Models\PaymentRecord;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KaspiPaymentReconciler
{
    protected ?Collection $openPaymentRecords = null;

    protected ?Collection $reportedPaymentRecords = null;

    public function __construct(
        protected OlympiadStatusNotifier $statusNotifier,
    ) {
    }

    public function reportPayment(OlympiadRequest $request, ?string $paidAt = null): PaymentRecord
    {
        $request->loadMissing(['subject', 'user', 'childProfile', 'paymentRecord']);

        $paymentRecord = PaymentRecord::updateOrCreate(
            ['olympiad_request_id' => $request->id],
            [
                'parent_id' => $request->user_id,
                'child_profile_id' => $request->child_profile_id,
                'subject_id' => $request->subject_id,
                'provider' => 'kaspi',
                'status' => $request->payment_status === 'paid' ? 'paid' : 'pending',
                'reconciliation_status' => $request->payment_status === 'paid' ? 'matched' : 'reported',
                'comment' => $request->paymentRecord?->comment ?: $this->buildPaymentComment($request),
                'customer_reported_at' => now(),
                'customer_paid_at' => $this->parseCustomerPaidAt($paidAt),
            ]
        );

        $this->flushCaches();

        if ($paymentRecord->status !== 'paid') {
            $this->reconcileReportedPaymentRecord($paymentRecord);
        }

        return $paymentRecord->fresh(['olympiadRequest.subject', 'olympiadRequest.user', 'olympiadRequest.childProfile']);
    }

    public function reconcileImportedRows(iterable $rows): array
    {
        $stats = [
            'matched' => 0,
            'needs_review' => 0,
            'skipped' => 0,
        ];

        foreach ($rows as $row) {
            $paymentImportRow = $row instanceof PaymentImportRow ? $row : PaymentImportRow::query()->find($row);

            if (!$paymentImportRow) {
                continue;
            }

            $result = $this->reconcileImportRow($paymentImportRow);
            $stats[$result] = ($stats[$result] ?? 0) + 1;
        }

        return $stats;
    }

    public function reconcileReportedPaymentRecord(PaymentRecord $paymentRecord): bool
    {
        if ($paymentRecord->status === 'paid') {
            return true;
        }

        $paymentRecord->loadMissing(['olympiadRequest.subject', 'olympiadRequest.user', 'olympiadRequest.childProfile']);

        $from = now()->subDays(7);
        $candidateRows = PaymentImportRow::query()
            ->where('provider', 'kaspi')
            ->whereIn('status', ['new', 'needs_review'])
            ->where(function ($query) use ($from) {
                $query->whereNull('paid_at')->orWhere('paid_at', '>=', $from);
            })
            ->orderByDesc('paid_at')
            ->get();

        foreach ($candidateRows as $row) {
            if ($this->matchesSpecificPaymentRecord($row, $paymentRecord)) {
                $this->markMatched($row, $paymentRecord);

                return true;
            }
        }

        return false;
    }

    protected function reconcileImportRow(PaymentImportRow $row): string
    {
        if ($row->status === 'matched' && $row->matched_payment_record_id) {
            return 'skipped';
        }

        $matchedPayment = $this->resolveByRequestReference($row)
            ?? $this->resolveByPaymentComment($row)
            ?? $this->resolveByReportedWindow($row);

        if ($matchedPayment) {
            $this->markMatched($row, $matchedPayment);

            return 'matched';
        }

        $this->markNeedsReview($row);

        return 'needs_review';
    }

    protected function resolveByRequestReference(PaymentImportRow $row): ?PaymentRecord
    {
        $comment = $this->normalizeText($row->comment);

        if ($comment === '') {
            return null;
        }

        $matches = $this->openPaymentRecords()
            ->filter(function (PaymentRecord $paymentRecord) use ($comment) {
                $requestReference = $paymentRecord->olympiadRequest?->public_id;

                return $requestReference && Str::contains($comment, mb_strtolower($requestReference));
            })
            ->values();

        return $matches->count() === 1 ? $matches->first() : null;
    }

    protected function resolveByPaymentComment(PaymentImportRow $row): ?PaymentRecord
    {
        $comment = $this->normalizeText($row->comment);

        if ($comment === '') {
            return null;
        }

        $matches = $this->openPaymentRecords()
            ->filter(function (PaymentRecord $paymentRecord) use ($comment) {
                $paymentComment = $this->normalizeText($paymentRecord->comment);

                return $paymentComment !== '' && Str::contains($comment, $paymentComment);
            })
            ->values();

        return $matches->count() === 1 ? $matches->first() : null;
    }

    protected function resolveByReportedWindow(PaymentImportRow $row): ?PaymentRecord
    {
        if ($row->amount === null || !$row->paid_at) {
            return null;
        }

        $windowStart = $row->paid_at->copy()->subMinutes(30);
        $windowEnd = $row->paid_at->copy()->addMinutes(30);

        $matches = $this->reportedPaymentRecords()
            ->filter(function (PaymentRecord $paymentRecord) use ($row, $windowStart, $windowEnd) {
                if ($paymentRecord->customer_paid_at === null) {
                    return false;
                }

                if ((string) $paymentRecord->amount !== (string) $row->amount) {
                    return false;
                }

                return $paymentRecord->customer_paid_at->between($windowStart, $windowEnd);
            })
            ->values();

        if ($matches->count() === 1) {
            return $matches->first();
        }

        if ($matches->count() > 1) {
            $matches->each(function (PaymentRecord $paymentRecord) {
                $paymentRecord->forceFill([
                    'reconciliation_status' => 'needs_review',
                ])->save();
            });

            $this->flushCaches();
        }

        return null;
    }

    protected function matchesSpecificPaymentRecord(PaymentImportRow $row, PaymentRecord $paymentRecord): bool
    {
        $requestReference = $paymentRecord->olympiadRequest?->public_id;
        $rowComment = $this->normalizeText($row->comment);

        if ($requestReference && $rowComment !== '' && Str::contains($rowComment, mb_strtolower($requestReference))) {
            return true;
        }

        $paymentComment = $this->normalizeText($paymentRecord->comment);

        if ($paymentComment !== '' && $rowComment !== '' && Str::contains($rowComment, $paymentComment)) {
            return true;
        }

        if ($paymentRecord->reconciliation_status !== 'reported' || $paymentRecord->customer_paid_at === null || $row->paid_at === null || $row->amount === null) {
            return false;
        }

        if ((string) $paymentRecord->amount !== (string) $row->amount) {
            return false;
        }

        return $paymentRecord->customer_paid_at->between(
            $row->paid_at->copy()->subMinutes(30),
            $row->paid_at->copy()->addMinutes(30),
        );
    }

    protected function markMatched(PaymentImportRow $row, PaymentRecord $paymentRecord): void
    {
        DB::transaction(function () use ($row, $paymentRecord) {
            $paymentRecord->loadMissing(['olympiadRequest.subject', 'olympiadRequest.user', 'olympiadRequest.childProfile']);

            $request = $paymentRecord->olympiadRequest;

            if (!$request) {
                return;
            }

            $previousStatus = $request->payment_status;
            $paidAt = $row->paid_at ?? now();

            $row->forceFill([
                'status' => 'matched',
                'matched_payment_record_id' => $paymentRecord->id,
            ])->save();

            $paymentRecord->forceFill([
                'provider' => 'kaspi',
                'status' => 'paid',
                'reconciliation_status' => 'matched',
                'external_reference' => $row->external_reference,
                'paid_at' => $paidAt,
                'customer_paid_at' => $paymentRecord->customer_paid_at ?: $paidAt,
            ])->save();

            $request->forceFill([
                'payment_status' => 'paid',
                'paid_at' => $paidAt,
            ])->save();

            try {
                OnboardingProgress::syncStep($request->user, 'approval_payment');
            } catch (\Throwable $exception) {
                report($exception);
            }

            $this->statusNotifier->paymentStatusChanged($request, 'paid', $previousStatus, false);
        });

        $this->flushCaches();
    }

    protected function markNeedsReview(PaymentImportRow $row): void
    {
        if ($row->status !== 'needs_review') {
            $row->forceFill([
                'status' => 'needs_review',
            ])->save();
        }
    }

    protected function openPaymentRecords(): Collection
    {
        if ($this->openPaymentRecords === null) {
            $this->openPaymentRecords = PaymentRecord::query()
                ->where('provider', 'kaspi')
                ->where('status', '!=', 'paid')
                ->whereHas('olympiadRequest')
                ->with(['olympiadRequest.subject', 'olympiadRequest.user', 'olympiadRequest.childProfile'])
                ->get();
        }

        return $this->openPaymentRecords;
    }

    protected function reportedPaymentRecords(): Collection
    {
        if ($this->reportedPaymentRecords === null) {
            $this->reportedPaymentRecords = PaymentRecord::query()
                ->where('provider', 'kaspi')
                ->where('status', '!=', 'paid')
                ->where('reconciliation_status', 'reported')
                ->whereNotNull('customer_paid_at')
                ->with(['olympiadRequest.subject', 'olympiadRequest.user', 'olympiadRequest.childProfile'])
                ->get();
        }

        return $this->reportedPaymentRecords;
    }

    protected function flushCaches(): void
    {
        $this->openPaymentRecords = null;
        $this->reportedPaymentRecords = null;
    }

    protected function normalizeText(?string $value): string
    {
        return mb_strtolower(trim((string) $value));
    }

    protected function parseCustomerPaidAt(?string $paidAt): Carbon
    {
        if (!$paidAt) {
            return now();
        }

        try {
            return Carbon::parse($paidAt);
        } catch (\Throwable) {
            return now();
        }
    }

    protected function buildPaymentComment(OlympiadRequest $request): string
    {
        $subject = $request->subject?->name ?? 'Олимпиада';
        $child = $request->childProfile?->full_name ?? trim($request->first_name . ' ' . $request->last_name);

        return trim("Заявка {$request->public_id} · {$subject} · {$child}");
    }
}
