<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChildProfile;
use App\Models\OlympiadRequest;
use App\Models\PaymentRecord;
use App\Models\PlatformNotification;
use App\Models\QuizResult;
use App\Models\TrainingAttempt;
use App\Support\OnboardingProgress;
use App\Support\StatusPresenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ProfileController extends Controller
{
    public function me(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Пользователь не авторизован.'], 401);
        }

        $user->load('childProfiles');

        $children = $user->childProfiles->map(fn (ChildProfile $child) => $this->mapChild($child));
        $olympiads = $this->buildOlympiadsPayload($request);
        $resultsCount = QuizResult::where('user_id', $user->id)->count();
        $paymentsCount = PaymentRecord::where('parent_id', $user->id)->count();
        $trainingCount = TrainingAttempt::where('parent_id', $user->id)->count();
        $notifications = PlatformNotification::query()
            ->where('user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (PlatformNotification $notification) => $this->mapNotification($notification));

        return response()->json([
            'user' => $user,
            'children' => $children,
            'stats' => [
                'children' => $children->count(),
                'olympiads' => $olympiads->count(),
                'results' => $resultsCount,
                'training_attempts' => $trainingCount,
                'payments' => $paymentsCount,
                'pending_requests' => $olympiads->where('status', 'pending')->count(),
                'ready_to_start' => $olympiads->filter(fn (array $item) => $item['status'] === 'approved' && $item['payment_status'] === 'paid' && !$item['completed'])->count(),
            ],
            'onboarding' => OnboardingProgress::payloadFor($user),
            'summary' => [
                'requests' => [
                    'pending' => $olympiads->where('status', 'pending')->count(),
                    'approved' => $olympiads->where('status', 'approved')->count(),
                    'rejected' => $olympiads->where('status', 'rejected')->count(),
                ],
                'payments' => [
                    'pending' => PaymentRecord::where('parent_id', $user->id)->where('status', 'pending')->count(),
                    'paid' => PaymentRecord::where('parent_id', $user->id)->where('status', 'paid')->count(),
                    'failed' => PaymentRecord::where('parent_id', $user->id)->where('status', 'failed')->count(),
                ],
            ],
            'current_task' => $this->resolveCurrentTask($user, $children, $olympiads),
            'notifications_preview' => $notifications,
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Пользователь не авторизован.'], 401);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:20|unique:users,phone,' . $user->id,
            'school' => 'required|string|max:255',
            'city' => 'required|string|max:255',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Профиль обновлён.',
            'user' => $user->fresh(),
        ]);
    }

    public function recentOlympiads(Request $request)
    {
        return response()->json($this->buildOlympiadsPayload($request, 5));
    }

    public function olympiads(Request $request)
    {
        return response()->json($this->buildOlympiadsPayload($request));
    }

    public function myResults(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Пользователь не авторизован.'], 401);
        }

        $childId = $this->resolveChildId($user->id, $request->input('child_profile_id'));

        $results = QuizResult::with(['quiz.subject', 'category', 'childProfile'])
            ->where('user_id', $user->id)
            ->when($childId, fn ($query) => $query->where('child_profile_id', $childId))
            ->latest()
            ->get();

        return response()->json($results->map(function (QuizResult $result) use ($user) {
            $percent = $result->total > 0
                ? (int) round(($result->score / $result->total) * 100)
                : 0;
            $resultStatus = $percent >= 60 ? 'passed' : 'failed';

            return [
                'id' => $result->public_id,
                'child_profile_id' => $result->childProfile?->public_id,
                'child_name' => $result->childProfile?->full_name ?? $user->name,
                'subject' => $result->quiz?->subject?->name ?? 'Неизвестно',
                'quiz_title' => $result->quiz?->title ?? 'Олимпиада',
                'date' => optional($result->created_at)->format('d.m.Y H:i'),
                'submitted_at' => optional($result->created_at)->toISOString(),
                'school' => $result->childProfile?->school ?: $user->school,
                'city' => $result->childProfile?->city ?: $user->city,
                'category_label' => $result->category?->label ?? 'Общая',
                'score' => $result->score,
                'total' => $result->total,
                'percent' => $percent,
                'status' => $resultStatus,
                'status_meta' => StatusPresenter::result($resultStatus),
                'certificate_url' => '/api/profile/results/' . $result->public_id . '/certificate',
                'certificate_preview_url' => '/profile/results/' . $result->public_id . '/certificate-preview',
            ];
        }));
    }

    public function payments(Request $request)
    {
        $user = $request->user();

        $payments = PaymentRecord::with(['childProfile', 'subject'])
            ->where('parent_id', $user->id)
            ->latest()
            ->get()
            ->map(fn (PaymentRecord $payment) => [
                'id' => $payment->public_id,
                'child_name' => $payment->childProfile?->full_name,
                'subject' => $payment->subject?->name,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'status' => $payment->status,
                'status_meta' => StatusPresenter::payment($payment->status),
                'external_reference' => $payment->external_reference,
                'comment' => $payment->comment,
                'paid_at' => optional($payment->paid_at)->toISOString(),
                'date' => optional($payment->created_at)->format('d.m.Y H:i'),
            ]);

        return response()->json($payments);
    }

    public function trainings(Request $request)
    {
        $user = $request->user();
        $childId = $this->resolveChildId($user->id, $request->input('child_profile_id'));

        $attempts = TrainingAttempt::with(['quiz.subject', 'childProfile', 'category'])
            ->where('parent_id', $user->id)
            ->when($childId, fn ($query) => $query->where('child_profile_id', $childId))
            ->latest()
            ->get()
            ->map(fn (TrainingAttempt $attempt) => [
                'id' => $attempt->id,
                'child_name' => $attempt->childProfile?->full_name,
                'subject' => $attempt->quiz?->subject?->name,
                'quiz_title' => $attempt->quiz?->title,
                'category_label' => $attempt->category?->label,
                'score' => $attempt->score,
                'total' => $attempt->total,
                'percent' => $attempt->total > 0 ? (int) round(($attempt->score / $attempt->total) * 100) : 0,
                'date' => optional($attempt->completed_at ?: $attempt->created_at)->format('d.m.Y H:i'),
            ]);

        return response()->json($attempts);
    }

    public function certificatePreview(Request $request, QuizResult $result)
    {
        $user = $request->user();

        if (!$user || $result->user_id !== $user->id) {
            abort(403);
        }

        $result->loadMissing(['quiz.subject', 'user', 'category', 'childProfile']);

        $percent = $result->total > 0
            ? (int) round(($result->score / $result->total) * 100)
            : 0;

        return response()->json([
            'id' => $result->public_id,
            'participant_name' => $result->childProfile?->full_name ?? $result->user?->name,
            'subject' => $result->quiz?->subject?->name ?? 'Олимпиада',
            'quiz_title' => $result->quiz?->title ?? 'Итоговый результат',
            'category' => $result->category?->label ?? 'Общая',
            'score' => $result->score,
            'total' => $result->total,
            'percent' => $percent,
            'school' => $result->childProfile?->school ?: $user->school,
            'city' => $result->childProfile?->city ?: $user->city,
            'date' => optional($result->created_at)->format('d.m.Y'),
            'download_url' => '/api/profile/results/' . $result->public_id . '/certificate',
            'status_meta' => StatusPresenter::result($percent >= 60 ? 'passed' : 'failed'),
        ]);
    }

    public function publicCertificateLookup(QuizResult $result)
    {
        $result->loadMissing(['quiz.subject', 'category', 'childProfile', 'user']);

        $percent = $result->total > 0
            ? (int) round(($result->score / $result->total) * 100)
            : 0;
        $status = $percent >= 60 ? 'passed' : 'failed';

        return response()->json([
            'id' => $result->public_id,
            'participant_name' => $result->childProfile?->full_name ?? $result->user?->name ?? 'Участник',
            'subject' => $result->quiz?->subject?->name ?? 'Олимпиада',
            'quiz_title' => $result->quiz?->title ?? 'Итоговый результат',
            'category' => $result->category?->label ?? 'Общая',
            'score' => $result->score,
            'total' => $result->total,
            'percent' => $percent,
            'date' => optional($result->created_at)->format('d.m.Y'),
            'school' => $result->childProfile?->school ?: ($result->user?->school ?: 'Школа не указана'),
            'city' => $result->childProfile?->city ?: ($result->user?->city ?: 'Город не указан'),
            'status' => $status,
            'status_meta' => StatusPresenter::result($status),
            'verification_note' => 'Сертификат найден в системе Online Olympiad и связан с завершённым результатом участника.',
        ]);
    }

    public function certificate(Request $request, QuizResult $result)
    {
        $user = $request->user();

        if (!$user || $result->user_id !== $user->id) {
            abort(403);
        }

        $result->loadMissing(['quiz.subject', 'user', 'category', 'childProfile']);

        $percent = $result->total > 0
            ? (int) round(($result->score / $result->total) * 100)
            : 0;

        $subject = $this->escapeSvg($result->quiz?->subject?->name ?? 'Олимпиада');
        $quizTitle = $this->escapeSvg($result->quiz?->title ?? 'Итоговый результат');
        $studentName = $this->escapeSvg($result->childProfile?->full_name ?? $result->user?->name ?? 'Участник');
        $school = $this->escapeSvg($result->childProfile?->school ?: ($result->user?->school ?: 'Школа не указана'));
        $city = $this->escapeSvg($result->childProfile?->city ?: ($result->user?->city ?: 'Город не указан'));
        $category = $this->escapeSvg($result->category?->label ?? 'Общая');
        $date = $this->escapeSvg(optional($result->created_at)->format('d.m.Y'));
        $score = $this->escapeSvg("{$result->score} / {$result->total} ({$percent}%)");

        $svg = <<<SVG
<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<svg xmlns="http://www.w3.org/2000/svg" width="1600" height="1130" viewBox="0 0 1600 1130">
  <defs>
    <linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#fffaf2"/>
      <stop offset="100%" stop-color="#fef3c7"/>
    </linearGradient>
    <linearGradient id="accent" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#b48d35"/>
      <stop offset="100%" stop-color="#d6b564"/>
    </linearGradient>
  </defs>
  <rect width="1600" height="1130" rx="48" fill="url(#bg)"/>
  <rect x="28" y="28" width="1544" height="1074" rx="36" fill="none" stroke="#b48d35" stroke-width="4"/>
  <rect x="68" y="68" width="1464" height="994" rx="28" fill="none" stroke="#e3c777" stroke-width="2" stroke-dasharray="10 10"/>
  <text x="800" y="170" text-anchor="middle" font-size="38" font-family="Georgia, 'Times New Roman', serif" fill="#8a6b25">ONLINE OLYMPIAD</text>
  <text x="800" y="270" text-anchor="middle" font-size="76" font-family="Georgia, 'Times New Roman', serif" fill="#6f5220">СЕРТИФИКАТ</text>
  <rect x="530" y="300" width="540" height="8" rx="4" fill="url(#accent)"/>
  <text x="800" y="390" text-anchor="middle" font-size="34" font-family="Arial, sans-serif" fill="#6f5c3a">Подтверждает участие и получение результата</text>
  <text x="800" y="505" text-anchor="middle" font-size="64" font-family="Georgia, 'Times New Roman', serif" fill="#111827">{$studentName}</text>
  <text x="800" y="570" text-anchor="middle" font-size="30" font-family="Arial, sans-serif" fill="#374151">{$school}, {$city}</text>
  <text x="800" y="660" text-anchor="middle" font-size="28" font-family="Arial, sans-serif" fill="#8a6b25">Предмет: {$subject}</text>
  <text x="800" y="712" text-anchor="middle" font-size="28" font-family="Arial, sans-serif" fill="#8a6b25">Категория: {$category}</text>
  <text x="800" y="764" text-anchor="middle" font-size="28" font-family="Arial, sans-serif" fill="#8a6b25">Олимпиада: {$quizTitle}</text>
  <text x="800" y="816" text-anchor="middle" font-size="28" font-family="Arial, sans-serif" fill="#8a6b25">Результат: {$score}</text>
  <text x="800" y="868" text-anchor="middle" font-size="28" font-family="Arial, sans-serif" fill="#8a6b25">Дата: {$date}</text>
  <text x="170" y="980" font-size="24" font-family="Arial, sans-serif" fill="#6b7280">Сертификат сгенерирован автоматически системой Online Olympiad</text>
  <text x="1230" y="980" font-size="24" font-family="Arial, sans-serif" fill="#6b7280">ID результата #{$result->public_id}</text>
</svg>
SVG;

        return Response::make($svg, 200, [
            'Content-Type' => 'image/svg+xml; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="certificate-result-' . $result->public_id . '.svg"',
        ]);
    }

    protected function buildOlympiadsPayload(Request $request, ?int $limit = null)
    {
        $user = $request->user();

        if (!$user) {
            return [];
        }

        $query = $user->olympiadRequests()
            ->with(['subject', 'childProfile'])
            ->latest();

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get()->map(fn ($item) => $this->mapOlympiadRequest($item));
    }

    protected function mapOlympiadRequest($item): array
    {
        $quizId = $item->subject?->quizzes()->value('id');
        $completed = false;

        if ($quizId) {
            $completed = QuizResult::where('user_id', $item->user_id)
                ->where('child_profile_id', $item->child_profile_id)
                ->where('quiz_id', $quizId)
                ->exists();
        }

        $countdownTarget = optional($item->subject?->start_date)->toDateString();
        $requestMeta = StatusPresenter::request($item->status);
        $paymentMeta = StatusPresenter::payment($item->payment_status);

        return [
            'id' => $item->public_id,
            'status' => $item->status,
            'status_meta' => $requestMeta,
            'payment_status' => $item->payment_status,
            'payment_status_meta' => $paymentMeta,
            'completed' => $completed,
            'disqualified' => (bool) $item->disqualified_at,
            'disqualified_at' => optional($item->disqualified_at)->toISOString(),
            'disqualification_reason' => $item->disqualification_reason,
            'countdown' => [
                'target' => $countdownTarget,
                'label' => $countdownTarget ? 'Старт олимпиады' : 'Дата старта появится позже',
            ],
            'next_action' => $this->resolveRequestNextAction($item->status, $item->payment_status, $completed),
            'child' => $item->childProfile ? $this->mapChild($item->childProfile) : null,
            'subject' => [
                'id' => $item->subject?->public_id,
                'name' => $item->subject?->name,
                'image' => $item->subject?->image,
                'description' => $item->subject?->description,
                'start_date' => $countdownTarget,
            ],
        ];
    }

    protected function mapChild(ChildProfile $child): array
    {
        return [
            'id' => $child->public_id,
            'first_name' => $child->first_name,
            'last_name' => $child->last_name,
            'full_name' => $child->full_name,
            'birth_date' => optional($child->birth_date)->toDateString(),
            'grade' => $child->grade,
            'school' => $child->school,
            'city' => $child->city,
            'language_preference' => $child->language_preference,
        ];
    }

    protected function mapNotification(PlatformNotification $notification): array
    {
        return [
            'id' => $notification->public_id,
            'type' => $notification->type,
            'title' => $notification->title,
            'body' => $notification->body,
            'action_url' => $notification->action_url,
            'status_key' => $notification->status_key,
            'read_at' => optional($notification->read_at)->toISOString(),
            'created_at' => optional($notification->created_at)->toISOString(),
            'date' => optional($notification->created_at)->format('d.m.Y H:i'),
        ];
    }

    protected function resolveCurrentTask($user, $children, $olympiads): array
    {
        if ($children->isEmpty()) {
            return [
                'key' => 'add_child',
                'title' => 'Добавьте первого участника',
                'description' => 'Создайте профиль ребёнка, чтобы можно было отправить заявку на олимпиаду.',
                'action_url' => '/profile',
                'action_label' => 'Добавить ребёнка',
                'tone' => 'warning',
            ];
        }

        $pendingRequest = $olympiads->firstWhere('status', 'pending');
        if ($pendingRequest) {
            return [
                'key' => 'wait_approval',
                'title' => 'Заявка ожидает проверки',
                'description' => 'Администратор проверяет данные участника. После этого откроется следующий шаг.',
                'action_url' => '/profile',
                'action_label' => 'Смотреть статус',
                'tone' => 'warning',
            ];
        }

        $paymentPending = $olympiads->first(fn (array $item) => $item['status'] === 'approved' && $item['payment_status'] !== 'paid');
        if ($paymentPending) {
            return [
                'key' => 'complete_payment',
                'title' => 'Подтвердите оплату',
                'description' => 'Заявка одобрена. Осталось оплатить участие или дождаться подтверждения платежа.',
                'action_url' => '/profile',
                'action_label' => 'Проверить оплату',
                'tone' => 'warning',
            ];
        }

        $readyQuiz = $olympiads->first(fn (array $item) => $item['status'] === 'approved' && $item['payment_status'] === 'paid' && !$item['completed']);
        if ($readyQuiz) {
            return [
                'key' => 'start_quiz',
                'title' => 'Можно начинать олимпиаду',
                'description' => 'Доступ к олимпиаде открыт. Перед стартом можно пройти тренировку.',
                'action_url' => '/subject',
                'action_label' => 'Открыть олимпиаду',
                'tone' => 'success',
            ];
        }

        if (QuizResult::where('user_id', $user->id)->exists()) {
            return [
                'key' => 'review_results',
                'title' => 'Результаты уже доступны',
                'description' => 'Откройте последние результаты и скачайте сертификат участника.',
                'action_url' => '/results',
                'action_label' => 'Открыть результаты',
                'tone' => 'success',
            ];
        }

        return [
            'key' => 'choose_olympiad',
            'title' => 'Выберите олимпиаду',
            'description' => 'Когда профиль ребёнка готов, можно перейти к выбору предмета и отправке заявки.',
            'action_url' => '/subject',
            'action_label' => 'Выбрать олимпиаду',
            'tone' => 'neutral',
        ];
    }

    protected function resolveRequestNextAction(?string $status, ?string $paymentStatus, bool $completed): string
    {
        if ($completed) {
            return 'Откройте результат и сертификат в кабинете.';
        }

        if ($status === 'approved' && $paymentStatus === 'paid') {
            return 'Можно начинать олимпиаду или открыть тренировку.';
        }

        if ($status === 'approved') {
            return 'Подтвердите оплату и дождитесь доступа к олимпиаде.';
        }

        if ($status === 'rejected') {
            return 'Проверьте данные участника и при необходимости свяжитесь с поддержкой.';
        }

        return 'Ожидайте проверки заявки администратором.';
    }

    protected function escapeSvg(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
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
}
