<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesChildId;
use App\Http\Controllers\Controller;
use App\Models\ChildProfile;
use App\Models\OlympiadRequest;
use App\Models\PaymentRecord;
use App\Models\PlatformNotification;
use App\Models\Quiz;
use App\Models\QuizResult;
use App\Models\TrainingAttempt;
use App\Support\OnboardingProgress;
use App\Support\StatusPresenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProfileController extends Controller
{
    use ResolvesChildId;
    public function me(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Пользователь не авторизован.'], 401);
        }

        $cacheKey = "profile:me:{$user->id}";

        $data = Cache::remember($cacheKey, 60, function () use ($user, $request) {
            $user->load('childProfiles');

            $children = $user->childProfiles->map(fn (ChildProfile $child) => $this->mapChild($child));
            $olympiads = $this->buildOlympiadsPayload($request);
            $resultsCount = QuizResult::where('user_id', $user->id)->count();
            $trainingCount = TrainingAttempt::where('parent_id', $user->id)->count();
            $paymentStats = PaymentRecord::where('parent_id', $user->id)
                ->selectRaw('COUNT(*) as total, SUM(status = "pending") as pending_count, SUM(status = "paid") as paid_count, SUM(status = "failed") as failed_count')
                ->first();
            $paymentsCount = (int) $paymentStats->total;
            $notifications = PlatformNotification::query()
                ->where('user_id', $user->id)
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (PlatformNotification $notification) => $this->mapNotification($notification));

            return [
                'user' => $user,
                'children' => $children,
                'stats' => [
                    'children' => $children->count(),
                    'olympiads' => $olympiads->count(),
                    'results' => $resultsCount,
                    'training_attempts' => $trainingCount,
                    'payments' => $paymentsCount,
                    'pending_requests' => $olympiads->where('status', 'pending')->count(),
                    'ready_to_start' => $olympiads->filter(fn (array $item) => $item['status'] === 'approved' && $item['payment_status'] === 'paid' && ($item['reconciliation_status'] ?? '') === 'matched' && !$item['completed'])->count(),
                ],
                'onboarding' => OnboardingProgress::payloadFor($user),
                'summary' => [
                    'requests' => [
                        'pending' => $olympiads->where('status', 'pending')->count(),
                        'approved' => $olympiads->where('status', 'approved')->count(),
                        'rejected' => $olympiads->where('status', 'rejected')->count(),
                    ],
                    'payments' => [
                        'pending' => (int) $paymentStats->pending_count,
                        'paid' => (int) $paymentStats->paid_count,
                        'failed' => (int) $paymentStats->failed_count,
                    ],
                ],
                'current_task' => $this->resolveCurrentTask($user, $children, $olympiads, $resultsCount),
                'notifications_preview' => $notifications,
            ];
        });

        return response()->json($data);
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

        Cache::forget("profile:me:{$user->id}");

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
            'participant'    => $this->transliterate($result->childProfile?->full_name ?? $result->user?->name ?? 'Participant'),
            'subject'        => $this->transliterate($result->quiz?->subject?->name ?? 'Olympiad'),
            'category'       => $this->transliterate($result->category?->label ?? 'General'),
            'school'         => $this->transliterate($result->childProfile?->school ?: ($result->user?->school ?: 'N/A')),
            'city'           => $this->transliterate($result->childProfile?->city ?: ($result->user?->city ?: 'N/A')),
            'score'          => "{$result->score} / {$result->total}",
            'percent'        => $percent,
            'date'           => optional($result->created_at)->format('d.m.Y') ?? date('d.m.Y'),
            'certificate_id' => $result->public_id,
            'passed'         => $percent >= 60,
        ]);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="certificate-' . $result->public_id . '.pdf"',
        ]);
    }

    protected function buildOlympiadsPayload(Request $request, ?int $limit = null): \Illuminate\Support\Collection
    {
        $user = $request->user();

        if (!$user) {
            return collect();
        }

        $childId = $this->resolveChildId($user->id, $request->input('child_profile_id'));

        $query = $user->olympiadRequests()
            ->with(['subject', 'childProfile', 'paymentRecord'])
            ->when($childId, fn ($query) => $query->where('child_profile_id', $childId))
            ->latest();

        if ($limit) {
            $query->limit($limit);
        }

        $requests = $query->get();

        if ($requests->isEmpty()) {
            return $requests;
        }

        $subjectIds = $requests->pluck('subject_id')->filter()->unique();
        $quizIdBySubject = Quiz::whereIn('subject_id', $subjectIds)
            ->select('id', 'subject_id')
            ->orderByDesc('id')
            ->get()
            ->groupBy('subject_id')
            ->map(fn ($group) => $group->first()->id);

        $quizIds = $quizIdBySubject->values()->filter();
        $completedSet = $quizIds->isNotEmpty()
            ? QuizResult::where('user_id', $user->id)
                ->whereIn('quiz_id', $quizIds)
                ->select('quiz_id', 'child_profile_id')
                ->get()
                ->mapWithKeys(fn ($r) => ["{$r->quiz_id}:{$r->child_profile_id}" => true])
                ->all()
            : [];

        return $requests->map(fn ($item) => $this->mapOlympiadRequest($item, $quizIdBySubject, $completedSet));
    }

    protected function mapOlympiadRequest($item, \Illuminate\Support\Collection $quizIdBySubject = null, array $completedSet = []): array
    {
        $quizId = $quizIdBySubject
            ? $quizIdBySubject->get($item->subject_id)
            : $item->subject?->quizzes()->value('id');

        $completed = $quizId && isset($completedSet["{$quizId}:{$item->child_profile_id}"]);

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

    protected function resolveCurrentTask($user, $children, $olympiads, int $resultsCount = 0): array
    {
        if ($children->isEmpty()) {
            return [
                'key' => 'add_child',
                'title' => 'Добавьте первого участника',
                'description' => 'Создайте профиль ребёнка, чтобы можно было отправить заявку на олимпиаду.',
                'action_url' => '/profile/children',
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
                'action_url' => '/profile/olympiads',
                'action_label' => 'Смотреть статус',
                'tone' => 'warning',
            ];
        }

        $paymentPending = $olympiads->first(fn (array $item) => $item['status'] === 'approved' && ($item['payment_status'] !== 'paid' || ($item['reconciliation_status'] ?? '') !== 'matched'));
        if ($paymentPending) {
            return [
                'key' => 'complete_payment',
                'title' => 'Подтвердите оплату',
                'description' => 'Заявка одобрена. Осталось оплатить участие или дождаться подтверждения платежа.',
                'action_url' => '/profile/olympiads',
                'action_label' => 'Проверить оплату',
                'tone' => 'warning',
            ];
        }

        $readyQuiz = $olympiads->first(fn (array $item) => $item['status'] === 'approved' && $item['payment_status'] === 'paid' && ($item['reconciliation_status'] ?? '') === 'matched' && !$item['completed']);
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
                'title' => 'Можно начинать олимпиаду',
                'description' => 'Доступ к олимпиаде открыт. Перед стартом можно пройти тренировку.',
                'action_url' => $readyActionUrl,
                'action_label' => 'Открыть олимпиаду',
                'tone' => 'success',
            ];
        }

        if ($resultsCount > 0) {
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

    protected function transliterate(string $value): string
    {
        $map = [
            'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'yo','ж'=>'zh',
            'з'=>'z','и'=>'i','й'=>'j','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o',
            'п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'kh','ц'=>'ts',
            'ч'=>'ch','ш'=>'sh','щ'=>'shch','ъ'=>'','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu',
            'я'=>'ya',
            'А'=>'A','Б'=>'B','В'=>'V','Г'=>'G','Д'=>'D','Е'=>'E','Ё'=>'Yo','Ж'=>'Zh',
            'З'=>'Z','И'=>'I','Й'=>'J','К'=>'K','Л'=>'L','М'=>'M','Н'=>'N','О'=>'O',
            'П'=>'P','Р'=>'R','С'=>'S','Т'=>'T','У'=>'U','Ф'=>'F','Х'=>'Kh','Ц'=>'Ts',
            'Ч'=>'Ch','Ш'=>'Sh','Щ'=>'Shch','Ъ'=>'','Ы'=>'Y','Ь'=>'','Э'=>'E','Ю'=>'Yu',
            'Я'=>'Ya',
            // Kazakh-specific
            'ә'=>'a','Ә'=>'A','ғ'=>'gh','Ғ'=>'Gh','қ'=>'q','Қ'=>'Q','ң'=>'ng','Ң'=>'Ng',
            'ө'=>'o','Ө'=>'O','ұ'=>'u','Ұ'=>'U','ү'=>'u','Ү'=>'U','һ'=>'h','Һ'=>'H',
            'і'=>'i','І'=>'I',
        ];

        return strtr($value, $map);
    }

    protected function escapeSvg(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    protected function buildPdfCertificate(array $data): string
    {
        $p       = $this->escapePdfText($data['participant']);
        $sub     = $this->escapePdfText($data['subject']);
        $cat     = $this->escapePdfText($data['category']);
        $school  = $this->escapePdfText($data['school']);
        $city    = $this->escapePdfText($data['city']);
        $score   = $this->escapePdfText($data['score']);
        $percent = (int) $data['percent'];
        $date    = $this->escapePdfText($data['date']);
        $certId  = $this->escapePdfText($data['certificate_id']);
        $passed  = (bool) $data['passed'];

        $resultBg   = $passed ? '0.13 0.47 0.29 rg' : '0.60 0.18 0.18 rg';
        $resultText = $passed ? 'PASSED' : 'FAILED';
        $statusLine = $passed ? 'Congratulations!' : 'Keep practicing!';

        $ops = [];

        // Cream background
        $ops[] = 'q';
        $ops[] = '0.992 0.973 0.928 rg';
        $ops[] = '0 0 842 595 re f';
        $ops[] = 'Q';

        // Outer gold border
        $ops[] = 'q';
        $ops[] = '0.792 0.675 0.239 RG';
        $ops[] = '3 w';
        $ops[] = '14 14 814 567 re S';
        $ops[] = 'Q';

        // Inner thin gold border
        $ops[] = 'q';
        $ops[] = '0.792 0.675 0.239 RG';
        $ops[] = '0.8 w';
        $ops[] = '22 22 798 551 re S';
        $ops[] = 'Q';

        // Left gold accent bar
        $ops[] = 'q';
        $ops[] = '0.792 0.675 0.239 rg';
        $ops[] = '22 22 6 434 re f';
        $ops[] = 'Q';

        // Header gold block
        $ops[] = 'q';
        $ops[] = '0.792 0.675 0.239 rg';
        $ops[] = '22 456 798 117 re f';
        $ops[] = 'Q';

        // Header white divider line
        $ops[] = 'q';
        $ops[] = '1 1 1 RG';
        $ops[] = '0.5 w';
        $ops[] = '42 468 m';
        $ops[] = '800 468 l';
        $ops[] = 'S';
        $ops[] = 'Q';

        // Result box
        $ops[] = 'q';
        $ops[] = $resultBg;
        $ops[] = '555 188 255 110 re f';
        $ops[] = 'Q';

        // Result box border (white)
        $ops[] = 'q';
        $ops[] = '1 1 1 RG';
        $ops[] = '0.8 w';
        $ops[] = '555 188 255 110 re S';
        $ops[] = 'Q';

        // Footer divider line
        $ops[] = 'q';
        $ops[] = '0.792 0.675 0.239 RG';
        $ops[] = '0.5 w';
        $ops[] = '42 67 m';
        $ops[] = '800 67 l';
        $ops[] = 'S';
        $ops[] = 'Q';

        // Header text (white)
        $ops[] = 'BT';
        $ops[] = '1 1 1 rg';
        $ops[] = '/F1 10 Tf';
        $ops[] = '1 0 0 1 268 540 Tm';
        $ops[] = '(E U R I C A) Tj';
        $ops[] = '/F2 36 Tf';
        $ops[] = '1 0 0 1 228 475 Tm';
        $ops[] = '(CERTIFICATE) Tj';
        $ops[] = 'ET';

        // Body text
        $ops[] = 'BT';
        $ops[] = '0.40 0.30 0.10 rg';
        $ops[] = '/F1 10 Tf';
        $ops[] = '1 0 0 1 48 428 Tm';
        $ops[] = '(This is to certify that) Tj';
        $ops[] = '0.07 0.07 0.07 rg';
        $ops[] = '/F2 22 Tf';
        $ops[] = '1 0 0 1 48 397 Tm';
        $ops[] = "({$p}) Tj";
        $ops[] = '0.40 0.40 0.40 rg';
        $ops[] = '/F1 11 Tf';
        $ops[] = '1 0 0 1 48 369 Tm';
        $ops[] = '(has participated on Eurica) Tj';
        $ops[] = '0.07 0.07 0.07 rg';
        $ops[] = '/F2 17 Tf';
        $ops[] = '1 0 0 1 48 341 Tm';
        $ops[] = "({$sub}) Tj";
        $ops[] = '0.45 0.45 0.45 rg';
        $ops[] = '/F1 10 Tf';
        $ops[] = '1 0 0 1 48 311 Tm';
        $ops[] = "(Category: {$cat}) Tj";
        $ops[] = '1 0 0 1 48 290 Tm';
        $ops[] = "(School: {$school}    City: {$city}) Tj";
        $ops[] = $passed ? '0.13 0.47 0.29 rg' : '0.60 0.18 0.18 rg';
        $ops[] = '/F2 11 Tf';
        $ops[] = '1 0 0 1 48 262 Tm';
        $ops[] = "({$statusLine}) Tj";
        $ops[] = 'ET';

        // Result box text (white)
        $ops[] = 'BT';
        $ops[] = '1 1 1 rg';
        $ops[] = '/F1 9 Tf';
        $ops[] = '1 0 0 1 608 284 Tm';
        $ops[] = '(RESULT) Tj';
        $ops[] = '/F2 32 Tf';
        $ops[] = '1 0 0 1 570 243 Tm';
        $ops[] = "({$percent}%) Tj";
        $ops[] = '/F2 12 Tf';
        $ops[] = '1 0 0 1 595 214 Tm';
        $ops[] = "({$resultText}) Tj";
        $ops[] = '/F1 9 Tf';
        $ops[] = '1 0 0 1 575 199 Tm';
        $ops[] = "(Score: {$score}) Tj";
        $ops[] = 'ET';

        // Footer text
        $ops[] = 'BT';
        $ops[] = '0.55 0.55 0.55 rg';
        $ops[] = '/F1 8 Tf';
        $ops[] = '1 0 0 1 48 50 Tm';
        $ops[] = "(Certificate ID: {$certId}) Tj";
        $ops[] = '1 0 0 1 330 50 Tm';
        $ops[] = "(Date: {$date}) Tj";
        $ops[] = '1 0 0 1 598 50 Tm';
        $ops[] = '(eurica.kz) Tj';
        $ops[] = 'ET';

        $stream = implode("\n", $ops);

        $objects = [];
        $objects[] = '1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj';
        $objects[] = '2 0 obj << /Type /Pages /Count 1 /Kids [3 0 R] >> endobj';
        $objects[] = '3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << /Font << /F1 5 0 R /F2 6 0 R >> >> /Contents 4 0 R >> endobj';
        $objects[] = '4 0 obj << /Length ' . strlen($stream) . " >> stream\n" . $stream . "\nendstream endobj";
        $objects[] = '5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj';
        $objects[] = '6 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >> endobj';

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

}
