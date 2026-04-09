<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OlympiadRequestResource;
use App\Models\ChildProfile;
use App\Models\OlympiadRequest;
use App\Models\PaymentRecord;
use App\Models\Subject;
use App\Support\NotificationWorkflow;
use App\Support\OnboardingProgress;
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

            $requestModel = $existing->fresh(['subject', 'user', 'childProfile']);
        } else {
            $requestModel = OlympiadRequest::create([
                ...$payload,
                'status' => 'approved',
                'payment_status' => 'pending',
            ]);
            $requestModel->load(['subject', 'user', 'childProfile']);

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
        }

        OnboardingProgress::syncStep($user, 'choose_subject');

        $payment = PaymentRecord::updateOrCreate(
            ['olympiad_request_id' => $requestModel->id],
            [
                'parent_id' => $user->id,
                'child_profile_id' => $child->id,
                'subject_id' => $requestModel->subject_id,
                'status' => $requestModel->payment_status,
                'paid_at' => $requestModel->payment_status === 'paid' ? $requestModel->paid_at : null,
            ]
        );

        return response()->json([
            'message' => $existing
                ? 'Участие обновлено. Можно переходить к оплате.'
                : 'Участие оформлено. Переходите к оплате.',
            'request' => new OlympiadRequestResource($requestModel),
            'payment' => $this->mapPayment($payment),
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
            ->where('user_id', $user->id)
            ->when($childId, fn ($query) => $query->where('child_profile_id', $childId))
            ->when($subjectId, fn ($query) => $query->where('subject_id', $subjectId))
            ->latest()
            ->first();

        return response()->json([
            'status' => $requestModel?->status,
            'payment_status' => $requestModel?->payment_status,
            'payment_url' => config('services.kaspi.payment_url'),
            'child_profile_id' => $requestModel?->childProfile?->public_id,
            'paid_at' => optional($requestModel?->paid_at)->toISOString(),
        ]);
    }

    public function index()
    {
        $requests = OlympiadRequest::query()
            ->whereIn('id', $this->latestRequestIdsQuery())
            ->with(['subject', 'user', 'childProfile'])
            ->latest()
            ->paginate(50);

        return OlympiadRequestResource::collection($requests);
    }

    public function show(OlympiadRequest $olympiadRequest)
    {
        $olympiadRequest->load(['subject', 'user', 'childProfile']);

        return response()->json([
            'id' => $olympiadRequest->public_id,
            'status' => $olympiadRequest->status,
            'payment_status' => $olympiadRequest->payment_status,
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

        if (!$user || !$user->is_admin) {
            return response()->json(['message' => 'Недостаточно прав.'], 403);
        }

        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $olympiadRequest->update([
            'status' => $request->string('status')->value(),
        ]);
        $olympiadRequest->load(['subject', 'user', 'childProfile']);

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

    public function updatePaymentStatus(Request $request, OlympiadRequest $olympiadRequest)
    {
        $user = $request->user();

        if (!$user || !$user->is_admin) {
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

        $olympiadRequest->update([
            'payment_status' => $status,
            'paid_at' => $paidAt,
        ]);

        $payment = PaymentRecord::updateOrCreate(
            ['olympiad_request_id' => $olympiadRequest->id],
            [
                'parent_id' => $olympiadRequest->user_id,
                'child_profile_id' => $olympiadRequest->child_profile_id,
                'subject_id' => $olympiadRequest->subject_id,
                'status' => $status,
                'amount' => $request->input('amount'),
                'external_reference' => $request->input('external_reference'),
                'comment' => $request->input('comment'),
                'paid_at' => $paidAt,
            ]
        );

        $olympiadRequest->load(['subject', 'user', 'childProfile']);

        if ($olympiadRequest->user) {
            if ($status === 'paid') {
                OnboardingProgress::syncStep($olympiadRequest->user, 'approval_payment');
            }

            NotificationWorkflow::createForUser(
                user: $olympiadRequest->user,
                type: match ($status) {
                    'paid' => 'payment_confirmed',
                    'failed' => 'payment_failed',
                    default => 'payment_pending',
                },
                title: match ($status) {
                    'paid' => 'Оплата подтверждена',
                    'failed' => 'Оплата не подтверждена',
                    default => 'Оплата ожидает подтверждения',
                },
                body: match ($status) {
                    'paid' => "Оплата за {$olympiadRequest->subject?->name} подтверждена. Доступ к олимпиаде открыт.",
                    'failed' => "Платёж по олимпиаде {$olympiadRequest->subject?->name} не был подтверждён.",
                    default => "Оплата по олимпиаде {$olympiadRequest->subject?->name} ожидает подтверждения.",
                },
                actionUrl: rtrim(config('app.url'), '/') . '/profile',
                statusKey: $status,
                payload: [
                    'action_label' => 'Проверить статус',
                    'context' => [
                        'Участник' => $olympiadRequest->childProfile?->full_name ?? trim($olympiadRequest->first_name . ' ' . $olympiadRequest->last_name),
                        'Предмет' => $olympiadRequest->subject?->name ?? 'Олимпиада',
                    ],
                ],
                sendEmail: in_array($status, ['paid', 'failed'], true)
            );
        }

        if ($status !== 'paid') {
            NotificationWorkflow::createForAdmins(
                type: 'payment_review_needed',
                title: 'Требуется проверка оплаты',
                body: "Статус платежа по предмету {$olympiadRequest->subject?->name} обновлён: {$status}.",
                actionUrl: '/admin/payments',
                statusKey: $status
            );
        }

        return response()->json([
            'message' => 'Статус оплаты обновлён.',
            'request' => new OlympiadRequestResource($olympiadRequest),
            'payment' => $this->mapPayment($payment),
            'payment_status' => $olympiadRequest->payment_status,
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
            'status' => $payment->status,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'paid_at' => optional($payment->paid_at)->toISOString(),
            'external_reference' => $payment->external_reference,
            'comment' => $payment->comment,
        ];
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
}
