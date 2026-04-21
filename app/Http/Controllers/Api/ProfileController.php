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
use Illuminate\Support\Str;

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

    public function mistakes(Request $request, QuizResult $result)
    {
        $user = $request->user();

        if (!$user || $result->user_id !== $user->id) {
            abort(403);
        }

        if (empty($result->answers)) {
            return response()->json([
                'message' => 'Разбор ошибок доступен только для новых попыток после обновления платформы.',
            ], 422);
        }

        $result->loadMissing([
            'quiz.subject',
            'category.questions.answers',
            'childProfile',
        ]);

        $savedAnswers = collect($result->answers ?? [])->mapWithKeys(
            fn ($answerId, $questionId) => [(int) $questionId => $answerId !== null ? (int) $answerId : null]
        );

        $items = $result->category?->questions
            ?->map(function ($question) use ($savedAnswers) {
                $selectedId = $savedAnswers->get($question->id);
                $correctAnswer = $question->answers->firstWhere('is_correct', true);
                $selectedAnswer = $selectedId ? $question->answers->firstWhere('id', $selectedId) : null;

                $status = $selectedId === null
                    ? 'skipped'
                    : (($correctAnswer && (int) $correctAnswer->id === (int) $selectedId) ? 'correct' : 'wrong');

                return [
                    'question_id' => $question->id,
                    'question' => $question->question,
                    'explanation' => $question->explanation,
                    'image' => $question->image,
                    'image_source' => $question->image_source,
                    'status' => $status,
                    'selected_answer' => $selectedAnswer ? [
                        'id' => $selectedAnswer->id,
                        'label' => $selectedAnswer->label,
                        'answer' => $selectedAnswer->answer,
                    ] : null,
                    'correct_answer' => $correctAnswer ? [
                        'id' => $correctAnswer->id,
                        'label' => $correctAnswer->label,
                        'answer' => $correctAnswer->answer,
                    ] : null,
                ];
            })
            ?->values() ?? collect();

        return response()->json([
            'id' => $result->public_id,
            'child_name' => $result->childProfile?->full_name ?? $user->name,
            'subject' => $result->quiz?->subject?->name ?? 'Олимпиада',
            'quiz_title' => $result->quiz?->title ?? 'Итоговый результат',
            'score' => $result->score,
            'total' => $result->total,
            'mistakes_count' => $items->where('status', '!=', 'correct')->count(),
            'questions_count' => $items->count(),
            'items' => $items,
        ]);
    }

    public function payments(Request $request)
    {
        $user = $request->user();
        $childId = $this->resolveChildId($user->id, $request->input('child_profile_id'));

        $payments = PaymentRecord::with(['childProfile', 'subject', 'olympiadRequest'])
            ->where('parent_id', $user->id)
            ->when($childId, fn ($query) => $query->where('child_profile_id', $childId))
            ->latest()
            ->get()
            ->map(fn (PaymentRecord $payment) => [
                'id' => $payment->public_id,
                'payment_reference' => $payment->olympiadRequest?->public_id,
                'child_name' => $payment->childProfile?->full_name,
                'subject' => $payment->subject?->name,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'status' => $payment->status,
                'reconciliation_status' => $payment->reconciliation_status,
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

        $pdf = $this->buildPdfCertificate([
            'ONLINE OLYMPIAD',
            'CERTIFICATE',
            'Participation and result confirmation',
            'Participant: ' . ($result->childProfile?->full_name ?? $result->user?->name ?? 'Participant'),
            'Subject: ' . ($result->quiz?->subject?->name ?? 'Olympiad'),
            'Category: ' . ($result->category?->label ?? 'General'),
            'Quiz: ' . ($result->quiz?->title ?? 'Final result'),
            'School: ' . ($result->childProfile?->school ?: ($result->user?->school ?: 'Not specified')),
            'City: ' . ($result->childProfile?->city ?: ($result->user?->city ?: 'Not specified')),
            'Score: ' . "{$result->score} / {$result->total} ({$percent}%)",
            'Date: ' . optional($result->created_at)->format('d.m.Y'),
            'Result ID: #' . $result->public_id,
        ]);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="certificate-result-' . $result->public_id . '.pdf"',
        ]);
    }

    protected function buildOlympiadsPayload(Request $request, ?int $limit = null)
    {
        $user = $request->user();

        if (!$user) {
            return [];
        }

        $childId = $this->resolveChildId($user->id, $request->input('child_profile_id'));

        $query = $user->olympiadRequests()
            ->with(['subject', 'childProfile', 'paymentRecord'])
            ->when($childId, fn ($query) => $query->where('child_profile_id', $childId))
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
            'reconciliation_status' => $item->paymentRecord?->reconciliation_status ?? 'awaiting_payment',
            'payment_status_meta' => $paymentMeta,
            'payment_reference' => $item->public_id,
            'payment_comment' => $item->paymentRecord?->comment ?? trim("Заявка {$item->public_id}"),
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
                'title' => 'Р”РѕР±Р°РІСЊС‚Рµ РїРµСЂРІРѕРіРѕ СѓС‡Р°СЃС‚РЅРёРєР°',
                'description' => 'РЎРѕР·РґР°Р№С‚Рµ РїСЂРѕС„РёР»СЊ СЂРµР±С‘РЅРєР°, С‡С‚РѕР±С‹ РјРѕР¶РЅРѕ Р±С‹Р»Рѕ РѕС‚РїСЂР°РІРёС‚СЊ Р·Р°СЏРІРєСѓ РЅР° РѕР»РёРјРїРёР°РґСѓ.',
                'action_url' => '/profile/children',
                'action_label' => 'Р”РѕР±Р°РІРёС‚СЊ СЂРµР±С‘РЅРєР°',
                'tone' => 'warning',
            ];
        }

        $pendingRequest = $olympiads->firstWhere('status', 'pending');
        if ($pendingRequest) {
            return [
                'key' => 'wait_approval',
                'title' => 'Р—Р°СЏРІРєР° РѕР¶РёРґР°РµС‚ РїСЂРѕРІРµСЂРєРё',
                'description' => 'РђРґРјРёРЅРёСЃС‚СЂР°С‚РѕСЂ РїСЂРѕРІРµСЂСЏРµС‚ РґР°РЅРЅС‹Рµ СѓС‡Р°СЃС‚РЅРёРєР°. РџРѕСЃР»Рµ СЌС‚РѕРіРѕ РѕС‚РєСЂРѕРµС‚СЃСЏ СЃР»РµРґСѓСЋС‰РёР№ С€Р°Рі.',
                'action_url' => '/profile/olympiads',
                'action_label' => 'РЎРјРѕС‚СЂРµС‚СЊ СЃС‚Р°С‚СѓСЃ',
                'tone' => 'warning',
            ];
        }

        $paymentPending = $olympiads->first(fn (array $item) => $item['status'] === 'approved' && $item['payment_status'] !== 'paid');
        if ($paymentPending) {
            return [
                'key' => 'complete_payment',
                'title' => 'РџРѕРґС‚РІРµСЂРґРёС‚Рµ РѕРїР»Р°С‚Сѓ',
                'description' => 'Р—Р°СЏРІРєР° РѕРґРѕР±СЂРµРЅР°. РћСЃС‚Р°Р»РѕСЃСЊ РѕРїР»Р°С‚РёС‚СЊ СѓС‡Р°СЃС‚РёРµ РёР»Рё РґРѕР¶РґР°С‚СЊСЃСЏ РїРѕРґС‚РІРµСЂР¶РґРµРЅРёСЏ РїР»Р°С‚РµР¶Р°.',
                'action_url' => '/profile/olympiads',
                'action_label' => 'РџСЂРѕРІРµСЂРёС‚СЊ РѕРїР»Р°С‚Сѓ',
                'tone' => 'warning',
            ];
        }

        $readyQuiz = $olympiads->first(fn (array $item) => $item['status'] === 'approved' && $item['payment_status'] === 'paid' && !$item['completed']);
        if ($readyQuiz) {
            $readyActionUrl = '/profile/olympiads';

            if (!empty($readyQuiz['subject']['id'])) {
                $readyActionUrl = '/quiz/' . $readyQuiz['subject']['id'];

                if (!empty($readyQuiz['child']['id'])) {
                    $readyActionUrl .= '?childId=' . $readyQuiz['child']['id'];
                }
            }

            return [
                'key' => 'start_quiz',
                'title' => 'РњРѕР¶РЅРѕ РЅР°С‡РёРЅР°С‚СЊ РѕР»РёРјРїРёР°РґСѓ',
                'description' => 'Р”РѕСЃС‚СѓРї Рє РѕР»РёРјРїРёР°РґРµ РѕС‚РєСЂС‹С‚. РџРµСЂРµРґ СЃС‚Р°СЂС‚РѕРј РјРѕР¶РЅРѕ РїСЂРѕР№С‚Рё С‚СЂРµРЅРёСЂРѕРІРєСѓ.',
                'action_url' => $readyActionUrl,
                'action_label' => 'РћС‚РєСЂС‹С‚СЊ РѕР»РёРјРїРёР°РґСѓ',
                'tone' => 'success',
            ];
        }

        if (QuizResult::where('user_id', $user->id)->exists()) {
            return [
                'key' => 'review_results',
                'title' => 'Р РµР·СѓР»СЊС‚Р°С‚С‹ СѓР¶Рµ РґРѕСЃС‚СѓРїРЅС‹',
                'description' => 'РћС‚РєСЂРѕР№С‚Рµ РїРѕСЃР»РµРґРЅРёРµ СЂРµР·СѓР»СЊС‚Р°С‚С‹ Рё СЃРєР°С‡Р°Р№С‚Рµ СЃРµСЂС‚РёС„РёРєР°С‚ СѓС‡Р°СЃС‚РЅРёРєР°.',
                'action_url' => '/results',
                'action_label' => 'РћС‚РєСЂС‹С‚СЊ СЂРµР·СѓР»СЊС‚Р°С‚С‹',
                'tone' => 'success',
            ];
        }

        return [
            'key' => 'choose_olympiad',
            'title' => 'Р’С‹Р±РµСЂРёС‚Рµ РѕР»РёРјРїРёР°РґСѓ',
            'description' => 'РљРѕРіРґР° РїСЂРѕС„РёР»СЊ СЂРµР±С‘РЅРєР° РіРѕС‚РѕРІ, РјРѕР¶РЅРѕ РїРµСЂРµР№С‚Рё Рє РІС‹Р±РѕСЂСѓ РїСЂРµРґРјРµС‚Р° Рё РѕС‚РїСЂР°РІРєРµ Р·Р°СЏРІРєРё.',
            'action_url' => '/subject',
            'action_label' => 'Р’С‹Р±СЂР°С‚СЊ РѕР»РёРјРїРёР°РґСѓ',
            'tone' => 'neutral',
        ];
    }

    protected function resolveRequestNextAction(?string $status, ?string $paymentStatus, bool $completed): string
    {
        if ($completed) {
            return 'РћС‚РєСЂРѕР№С‚Рµ СЂРµР·СѓР»СЊС‚Р°С‚ Рё СЃРµСЂС‚РёС„РёРєР°С‚ РІ РєР°Р±РёРЅРµС‚Рµ.';
        }

        if ($status === 'approved' && $paymentStatus === 'paid') {
            return 'РњРѕР¶РЅРѕ РЅР°С‡РёРЅР°С‚СЊ РѕР»РёРјРїРёР°РґСѓ РёР»Рё РѕС‚РєСЂС‹С‚СЊ С‚СЂРµРЅРёСЂРѕРІРєСѓ.';
        }

        if ($status === 'approved') {
            return 'РџРѕРґС‚РІРµСЂРґРёС‚Рµ РѕРїР»Р°С‚Сѓ Рё РґРѕР¶РґРёС‚РµСЃСЊ РґРѕСЃС‚СѓРїР° Рє РѕР»РёРјРїРёР°РґРµ.';
        }

        if ($status === 'rejected') {
            return 'РџСЂРѕРІРµСЂСЊС‚Рµ РґР°РЅРЅС‹Рµ СѓС‡Р°СЃС‚РЅРёРєР° Рё РїСЂРё РЅРµРѕР±С…РѕРґРёРјРѕСЃС‚Рё СЃРІСЏР¶РёС‚РµСЃСЊ СЃ РїРѕРґРґРµСЂР¶РєРѕР№.';
        }

        return 'РћР¶РёРґР°Р№С‚Рµ РїСЂРѕРІРµСЂРєРё Р·Р°СЏРІРєРё Р°РґРјРёРЅРёСЃС‚СЂР°С‚РѕСЂРѕРј.';
    }

    protected function escapeSvg(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    protected function buildPdfCertificate(array $lines): string
    {
        $y = 520;
        $content = ["BT", "/F1 18 Tf"];

        foreach ($lines as $line) {
            $safeLine = $this->escapePdfText(Str::ascii($line));
            $content[] = sprintf('1 0 0 1 56 %d Tm (%s) Tj', $y, $safeLine);
            $y -= 34;
        }

        $content[] = 'ET';
        $stream = implode("\n", $content);

        $objects = [];
        $objects[] = '1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj';
        $objects[] = '2 0 obj << /Type /Pages /Count 1 /Kids [3 0 R] >> endobj';
        $objects[] = '3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >> endobj';
        $objects[] = '4 0 obj << /Length ' . strlen($stream) . " >> stream\n" . $stream . "\nendstream endobj";
        $objects[] = '5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj';

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object . "\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= str_pad((string) $offsets[$i], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }

        $pdf .= "trailer << /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }

    protected function escapePdfText(string $value): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $value);
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
