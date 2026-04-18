<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OlympiadRequestResource;
use App\Models\ChildProfile;
use App\Models\OlympiadRequest;
use App\Models\PaymentRecord;
use App\Models\Quiz;
use App\Models\Subject;
use App\Support\KaspiPaymentReconciler;
use App\Support\NotificationWorkflow;
use App\Support\OnboardingProgress;
use App\Support\OlympiadStatusNotifier;
use Illuminate\Http\Request;

class OlympiadRequestController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Требуется авторизация.'], 401);
        }

        $data = $request->validate([
            'subject_id' => 'required|string',
            'child_profile_id' => 'nullable|string',
            'first_name' => 'required_without:child_profile_id|string|max:255',
            'last_name' => 'required_without:child_profile_id|string|max:255',
            'birth_date' => 'nullable|date',
            'grade' => 'required_without:child_profile_id|integer|between:3,11',
            'language' => 'required|string|max:10',
            'parent_name' => 'required|string|max:255',
            'parent_phone' => 'required|string|max:20',
            'parent_email' => 'required|email|max:255',
        ]);

        $child = $this->resolveChildProfile($request, $user->id, $data);
        $subjectId = $this->resolveSubjectId($data['subject_id']);
        $price = $this->resolveQuizPrice($subjectId);
        $birthDate = $data['birth_date'] ?? optional($child->birth_date)->toDateString() ?? now()->toDateString();

        $payload = [
            'user_id' => $user->id,
            'child_profile_id' => $child->id,
            'subject_id' => $subjectId,
            'first_name' => $child->first_name,
            'last_name' => $child->last_name,
            'birth_date' => $birthDate,
            'grade' => $child->grade,
            'language' => $data['language'],
            'parent_name' => $data['parent_name'],
            'parent_phone' => $data['parent_phone'],
            'parent_email' => $data['parent_email'],
        ];

        $existing = OlympiadRequest::query()
            ->where('user_id', $user->id)
            ->where('child_profile_id', $child->id)
            ->where('subject_id', $subjectId)
            ->latest()
            ->first();

        if ($existing) {
            $existing->update([
                ...$payload,
                'status' => 'approved',
                'payment_status' => $existing->payment_status === 'paid' ? 'paid' : 'pending',
                'paid_at' => $existing->payment_status === 'paid' ? $existing->paid_at : null,
            ]);

            $requestModel = $existing->fresh(['subject', 'user', 'childProfile', 'paymentRecord']);
        } else {
            $requestModel = OlympiadRequest::create([
                ...$payload,
                'status' => 'approved',
                'payment_status' => 'pending',
            ]);
            $requestModel->load(['subject', 'user', 'childProfile', 'paymentRecord']);

            try {
                NotificationWorkflow::createForUser(
                    user: $user,
                    type: 'olympiad_request_approved',
                    title: 'Участие оформлено',
                    body: "Участие в олимпиаде по предмету {$requestModel->subject?->name} оформлено. Можно переходить к оплате.",
                    actionUrl: rtrim(config('app.url'), '/') . '/profile',
                    statusKey: 'approved',
                    payload: [
                        'action_label' => 'Открыть кабинет',
                        'context' => [
                            'Участник' => $child->full_name,
                            'Предмет' => $requestModel->subject?->name ?? 'Олимпиада',
                        ],
                    ],
                    sendEmail: true
                );

                NotificationWorkflow::createForAdmins(
                    type: 'new_payment_pending',
                    title: 'Новый участник ждёт оплату',
                    body: "Оформлено участие {$user->name} по предмету {$requestModel->subject?->name}. Ожидается оплата.",
                    actionUrl: '/admin/payments',
                    statusKey: 'pending'
                );
            } catch (\Throwable $notificationError) {
                report($notificationError);
            }
        }

        try {
            OnboardingProgress::syncStep($user, 'choose_subject');
        } catch (\Throwable $onboardingError) {
            report($onboardingError);
        }

        $payment = $this->syncPaymentRecordForRequest($requestModel, $user->id, $child->id, $price);

        return response()->json([
            'message' => $existing
                ? 'Участие обновлено. Можно переходить к оплате.'
                : 'Участие оформлено. Переходите к оплате.',
            'request' => new OlympiadRequestResource($requestModel),
            'payment' => $this->mapPayment($payment),
            'payment_reference' => $requestModel->public_id,
            'payment_comment' => $this->buildPaymentComment($requestModel),
            'redirect_to_quiz' => $requestModel->payment_status === 'paid',
            'payment_url' => config('services.kaspi.payment_url'),
        ]);
    }

    public function status(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['status' => null]);
        }

        $childId = $this->resolveChildId($user->id, $request->input('child_profile_id'));
        $subjectId = $request->filled('subject_id')
            ? $this->resolveSubjectId((string) $request->input('subject_id'))
            : null;

        $requestModel = OlympiadRequest::query()
            ->with(['childProfile', 'paymentRecord'])
            ->where('user_id', $user->id)
            ->when($childId, fn ($query) => $query->where('child_profile_id', $childId))
            ->when($subjectId, fn ($query) => $query->where('subject_id', $subjectId))
            ->latest()
            ->first();

        return response()->json([
            'status' => $requestModel?->status,
            'payment_status' => $requestModel?->payment_status,
            'reconciliation_status' => $requestModel?->paymentRecord?->reconciliation_status ?? 'awaiting_payment',
            'subject_id' => $requestModel?->subject?->public_id,
            'payment_reference' => $requestModel?->public_id,
            'payment_comment' => $requestModel ? $this->buildPaymentComment($requestModel) : null,
            'payment_url' => config('services.kaspi.payment_url'),
            'child_profile_id' => $requestModel?->childProfile?->public_id,
            'paid_at' => optional($requestModel?->paid_at)->toISOString(),
        ]);
    }

    public function paymentReport(Request $request, OlympiadRequest $olympiadRequest, KaspiPaymentReconciler $reconciler)
    {
        $user = $request->user();

        if (!$user || $olympiadRequest->user_id !== $user->id) {
            return response()->json(['message' => 'Недостаточно прав.'], 403);
        }

        $validated = $request->validate([
            'paid_at' => 'nullable|string|max:255',
        ]);

        $payment = $reconciler->reportPayment($olympiadRequest, $validated['paid_at'] ?? null);
        $olympiadRequest = $olympiadRequest->fresh(['subject', 'user', 'childProfile', 'paymentRecord']);

        return response()->json([
            'message' => 'Платёж отмечен и отправлен на сверку.',
            'request' => new OlympiadRequestResource($olympiadRequest),
            'payment' => $this->mapPayment($payment),
            'payment_status' => $olympiadRequest->payment_status,
            'reconciliation_status' => $payment->reconciliation_status,
            'paid_at' => optional($olympiadRequest->paid_at)->toISOString(),
        ]);
    }

    public function index()
    {
        $requests = OlympiadRequest::query()
            ->whereIn('id', $this->latestRequestIdsQuery())
            ->with(['subject', 'user', 'childProfile', 'paymentRecord'])
            ->latest()
            ->paginate(50);

        return OlympiadRequestResource::collection($requests);
    }

    public function show(OlympiadRequest $olympiadRequest)
    {
        $olympiadRequest->load(['subject', 'user', 'childProfile', 'paymentRecord']);

        return response()->json([
            'id' => $olympiadRequest->public_id,
            'status' => $olympiadRequest->status,
            'payment_status' => $olympiadRequest->payment_status,
            'reconciliation_status' => $olympiadRequest->paymentRecord?->reconciliation_status ?? 'awaiting_payment',
            'paid_at' => optional($olympiadRequest->paid_at)->toISOString(),
            'completed' => $olympiadRequest->completed,
            'first_name' => $olympiadRequest->first_name,
            'last_name' => $olympiadRequest->last_name,
            'birth_date' => optional($olympiadRequest->birth_date)->toDateString(),
            'grade' => $olympiadRequest->grade,
            'language' => $olympiadRequest->language,
            'parent_name' => $olympiadRequest->parent_name,
            'parent_phone' => $olympiadRequest->parent_phone,
            'parent_email' => $olympiadRequest->parent_email,
            'subject' => [
                'id' => $olympiadRequest->subject?->public_id,
                'name' => $olympiadRequest->subject?->name,
            ],
            'child' => $olympiadRequest->childProfile ? [
                'id' => $olympiadRequest->childProfile->public_id,
                'full_name' => $olympiadRequest->childProfile->full_name,
                'grade' => $olympiadRequest->childProfile->grade,
            ] : null,
            'user' => [
                'id' => $olympiadRequest->user?->public_id,
                'name' => $olympiadRequest->user?->name,
                'email' => $olympiadRequest->user?->email,
            ],
            'created_at' => optional($olympiadRequest->created_at)->toISOString(),
            'payment_reference' => $olympiadRequest->public_id,
            'payment_comment' => $this->buildPaymentComment($olympiadRequest),
            'payment_url' => config('services.kaspi.payment_url'),
        ]);
    }

    public function stats()
    {
        $latestIds = $this->latestRequestIdsQuery();

        return response()->json([
            'total' => OlympiadRequest::whereIn('id', $latestIds)->count(),
            'pending' => OlympiadRequest::whereIn('id', $latestIds)->where('status', 'pending')->count(),
            'approved' => OlympiadRequest::whereIn('id', $latestIds)->where('status', 'approved')->count(),
            'rejected' => OlympiadRequest::whereIn('id', $latestIds)->where('status', 'rejected')->count(),
            'completed' => OlympiadRequest::whereIn('id', $latestIds)->where('completed', true)->count(),
            'children' => ChildProfile::count(),
            'payments_paid' => PaymentRecord::whereIn('olympiad_request_id', $latestIds)->where('status', 'paid')->count(),
        ]);
    }

    public function approveReject(Request $request, OlympiadRequest $olympiadRequest)
    {
        return $this->updateStatus($request, $olympiadRequest);
    }

    public function updateStatus(Request $request, OlympiadRequest $olympiadRequest)
    {
        $user = $request->user();

        if (!$user || !$user->hasAdminCapability('requests')) {
            return response()->json(['message' => 'Недостаточно прав.'], 403);
        }

        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $olympiadRequest->update([
            'status' => $request->string('status')->value(),
        ]);
        $olympiadRequest->load(['subject', 'user', 'childProfile', 'paymentRecord']);

        if ($olympiadRequest->user) {
            $isApproved = $olympiadRequest->status === 'approved';

            NotificationWorkflow::createForUser(
                user: $olympiadRequest->user,
                type: $isApproved ? 'olympiad_request_approved' : 'olympiad_request_rejected',
                title: $isApproved ? 'Участие подтверждено' : 'Участие отклонено',
                body: $isApproved
                    ? "Участие по предмету {$olympiadRequest->subject?->name} подтверждено. Следующий шаг — оплата."
                    : "Участие по предмету {$olympiadRequest->subject?->name} отклонено. Проверьте данные участника или свяжитесь с поддержкой.",
                actionUrl: rtrim(config('app.url'), '/') . '/profile',
                statusKey: $olympiadRequest->status,
                payload: [
                    'action_label' => 'Открыть кабинет',
                    'context' => [
                        'Участник' => $olympiadRequest->childProfile?->full_name ?? trim($olympiadRequest->first_name . ' ' . $olympiadRequest->last_name),
                        'Предмет' => $olympiadRequest->subject?->name ?? 'Олимпиада',
                    ],
                ],
                sendEmail: true
            );
        }

        return response()->json([
            'message' => 'Статус участия обновлён.',
            'request' => new OlympiadRequestResource($olympiadRequest),
        ]);
    }

    public function updatePaymentStatus(Request $request, OlympiadRequest $olympiadRequest, OlympiadStatusNotifier $statusNotifier)
    {
        $user = $request->user();

        if (!$user || !$user->hasAdminCapability('payments')) {
            return response()->json(['message' => 'Недостаточно прав.'], 403);
        }

        $request->validate([
            'payment_status' => 'required|in:pending,paid,failed',
            'amount' => 'nullable|numeric|min:0',
            'external_reference' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:1000',
        ]);

        $status = $request->string('payment_status')->value();
        $paidAt = $status === 'paid' ? now() : null;
        $previousStatus = $olympiadRequest->payment_status;

        $olympiadRequest->update([
            'payment_status' => $status,
            'paid_at' => $paidAt,
        ]);

        $payment = PaymentRecord::firstOrNew(['olympiad_request_id' => $olympiadRequest->id]);
        $payment->fill([
            'parent_id' => $olympiadRequest->user_id,
            'child_profile_id' => $olympiadRequest->child_profile_id,
            'subject_id' => $olympiadRequest->subject_id,
            'provider' => 'kaspi',
            'status' => $status,
            'amount' => $request->filled('amount') ? $request->input('amount') : ($payment->amount ?? $this->resolveQuizPrice($olympiadRequest->subject_id)),
            'reconciliation_status' => $this->manualReconciliationStatus($status),
            'external_reference' => $request->input('external_reference', $status === 'paid' ? $payment->external_reference : null),
            'comment' => $request->input('comment', $payment->comment ?: $this->buildPaymentComment($olympiadRequest)),
            'paid_at' => $paidAt,
            'customer_reported_at' => $status === 'pending' ? null : $payment->customer_reported_at,
            'customer_paid_at' => $status === 'pending' ? null : $payment->customer_paid_at,
        ]);
        $payment->save();

        $olympiadRequest->load(['subject', 'user', 'childProfile', 'paymentRecord']);

        if ($olympiadRequest->user) {
            if ($status === 'paid') {
                try {
                    OnboardingProgress::syncStep($olympiadRequest->user, 'approval_payment');
                } catch (\Throwable $onboardingError) {
                    report($onboardingError);
                }
            }

            $statusNotifier->paymentStatusChanged(
                $olympiadRequest,
                $status,
                $previousStatus,
                $status !== 'paid'
            );
        }

        return response()->json([
            'message' => 'Статус оплаты обновлён.',
            'request' => new OlympiadRequestResource($olympiadRequest),
            'payment' => $this->mapPayment($payment),
            'payment_status' => $olympiadRequest->payment_status,
            'reconciliation_status' => $payment->reconciliation_status,
            'paid_at' => optional($olympiadRequest->paid_at)->toISOString(),
        ]);
    }

    public function destroy(OlympiadRequest $olympiadRequest)
    {
        $olympiadRequest->delete();

        return response()->json([
            'message' => 'Запись участия удалена.',
        ]);
    }

    protected function resolveChildProfile(Request $request, int $parentId, array $data): ChildProfile
    {
        if (!empty($data['child_profile_id'])) {
            $child = ChildProfile::query()
                ->where('parent_id', $parentId)
                ->where('public_id', $data['child_profile_id'])
                ->firstOrFail();

            $child->update([
                'birth_date' => $data['birth_date'] ?? $child->birth_date,
                'grade' => $data['grade'] ?? $child->grade,
                'language_preference' => $data['language'] ?? $child->language_preference,
                'school' => $request->user()->school,
                'city' => $request->user()->city,
            ]);

            return $child;
        }

        return ChildProfile::updateOrCreate(
            [
                'parent_id' => $parentId,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
            ],
            [
                'birth_date' => $data['birth_date'] ?? null,
                'grade' => $data['grade'] ?? null,
                'language_preference' => $data['language'] ?? 'ru',
                'school' => $request->user()->school,
                'city' => $request->user()->city,
            ]
        );
    }

    protected function mapPayment(PaymentRecord $payment): array
    {
        return [
            'id' => $payment->public_id,
            'provider' => $payment->provider,
            'status' => $payment->status,
            'reconciliation_status' => $payment->reconciliation_status,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'paid_at' => optional($payment->paid_at)->toISOString(),
            'customer_paid_at' => optional($payment->customer_paid_at)->toISOString(),
            'external_reference' => $payment->external_reference,
            'comment' => $payment->comment,
        ];
    }

    protected function buildPaymentComment(OlympiadRequest $request): string
    {
        $subject = $request->subject?->name ?? 'Олимпиада';
        $child = $request->childProfile?->full_name ?? trim($request->first_name . ' ' . $request->last_name);

        return trim("Заявка {$request->public_id} · {$subject} · {$child}");
    }

    protected function latestRequestIdsQuery()
    {
        return OlympiadRequest::query()
            ->selectRaw('MAX(id)')
            ->groupBy('user_id', 'child_profile_id', 'subject_id');
    }

    protected function resolveChildId(int $userId, mixed $childPublicId): ?int
    {
        if (!$childPublicId) {
            return null;
        }

        return ChildProfile::query()
            ->where('parent_id', $userId)
            ->where('public_id', (string) $childPublicId)
            ->value('id');
    }

    protected function resolveSubjectId(string $subjectKey): int
    {
        return Subject::query()
            ->where('public_id', $subjectKey)
            ->valueOrFail('id');
    }

    protected function resolveQuizPrice(int $subjectId): int
    {
        return (int) (Quiz::query()
            ->where('subject_id', $subjectId)
            ->latest('id')
            ->value('price') ?? 0);
    }

    protected function syncPaymentRecordForRequest(OlympiadRequest $requestModel, int $parentId, int $childId, int $price): PaymentRecord
    {
        $payment = PaymentRecord::firstOrNew(['olympiad_request_id' => $requestModel->id]);
        $isPaid = $requestModel->payment_status === 'paid';

        $payment->fill([
            'parent_id' => $parentId,
            'child_profile_id' => $childId,
            'subject_id' => $requestModel->subject_id,
            'amount' => $price,
            'currency' => $payment->currency ?: 'KZT',
            'provider' => 'kaspi',
            'status' => $isPaid ? 'paid' : 'pending',
            'reconciliation_status' => $isPaid ? 'matched' : 'awaiting_payment',
            'external_reference' => $isPaid ? $payment->external_reference : null,
            'comment' => $this->buildPaymentComment($requestModel),
            'paid_at' => $isPaid ? $requestModel->paid_at : null,
            'customer_reported_at' => $isPaid ? $payment->customer_reported_at : null,
            'customer_paid_at' => $isPaid ? ($payment->customer_paid_at ?: $requestModel->paid_at) : null,
        ]);
        $payment->save();

        return $payment->fresh();
    }

    protected function manualReconciliationStatus(string $status): string
    {
        return match ($status) {
            'paid' => 'matched',
            'failed' => 'needs_review',
            default => 'awaiting_payment',
        };
    }
}
