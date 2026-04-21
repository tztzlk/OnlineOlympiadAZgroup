<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChildProfile;
use App\Models\OlympiadRequest;
use App\Models\Quiz;
use App\Models\QuizCategory;
use App\Models\QuizResult;
use App\Models\Subject;
use App\Support\NotificationWorkflow;
use App\Support\OnboardingProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizController extends Controller
{
    public function getSubjects(Request $request)
    {
        $user = Auth::user();
        $childId = $this->resolveChildId($user->id, $request->input('child_profile_id'));

        $approvedSubjects = OlympiadRequest::query()
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('payment_status', 'paid')
            ->when($childId > 0, fn ($query) => $query->where('child_profile_id', $childId))
            ->pluck('subject_id');

        return response()->json(
            Quiz::with('subject')
                ->withCount('questions')
                ->where('is_published', true)
                ->whereIn('subject_id', $approvedSubjects)
                ->get()
        );
    }

    public function getQuiz(Request $request, string $subjectId)
    {
        $user = Auth::user();
        $childId = $this->resolveChildId($user->id, $request->input('child_profile_id'));
        $resolvedSubjectId = $this->resolveSubjectId($subjectId);
        $requestRecord = $this->resolveRequest($user->id, $resolvedSubjectId, $childId);

        if (!$requestRecord) {
            return response()->json(['message' => 'Заявка на эту олимпиаду не найдена.'], 404);
        }

        if ($requestRecord->status === 'rejected') {
            return response()->json(['message' => 'Заявка отклонена. Доступ к олимпиаде закрыт.'], 403);
        }

        if ($requestRecord->status !== 'approved') {
            return response()->json(['message' => 'Заявка ещё не одобрена администратором.'], 403);
        }

        if ($requestRecord->disqualified_at) {
            return response()->json(['message' => 'Попытка аннулирована из-за нарушения правил тестирования.'], 403);
        }

        if ($requestRecord->payment_status !== 'paid') {
            return response()->json([
                'message' => 'Оплата ещё не подтверждена. После проверки оплаты доступ к олимпиаде откроется.',
                'payment_required' => true,
                'payment_status' => $requestRecord->payment_status,
                'reconciliation_status' => $requestRecord->paymentRecord?->reconciliation_status ?? 'awaiting_payment',
                'payment_url' => config('services.kaspi.payment_url'),
            ], 402);
        }

        $quiz = Quiz::query()
            ->where('subject_id', $resolvedSubjectId)
            ->where('is_published', true)
            ->with([
                'subject',
                'categories.questions.answers:id,question_id,label,position,answer,is_correct',
            ])
            ->first();

        if (!$quiz) {
            return response()->json(['message' => 'Олимпиада по этому предмету пока не опубликована.'], 404);
        }

        $alreadySubmitted = QuizResult::query()
            ->where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->when($requestRecord->child_profile_id, fn ($query) => $query->where('child_profile_id', $requestRecord->child_profile_id))
            ->exists();

        $category = $this->pickCategoryForGrade($quiz, $requestRecord->grade);

        if (!$category) {
            return response()->json([
                'message' => 'Для указанного класса пока не настроен комплект заданий.',
            ], 422);
        }

        return response()->json([
            'id' => $quiz->public_id,
            'subject_id' => $quiz->subject?->public_id,
            'title' => $quiz->title,
            'description' => $quiz->description,
            'time_limit' => $quiz->time_limit,
            'already_submitted' => $alreadySubmitted,
            'child_profile_id' => $requestRecord->childProfile?->public_id,
            'child' => $requestRecord->childProfile ? [
                'id' => $requestRecord->childProfile->public_id,
                'full_name' => $requestRecord->childProfile->full_name,
                'grade' => $requestRecord->childProfile->grade,
            ] : null,
            'warning' => 'Во время олимпиады нельзя переключать вкладку, окно, выходить из полноэкранного режима или сворачивать браузер.',
            'warning_rules' => [
                'Запрещено переключать вкладку, окно, выходить из полноэкранного режима или сворачивать браузер.',
                'Нельзя использовать подсказки, списывать и обращаться к сторонней помощи.',
                'При нарушении правил попытка аннулируется автоматически.',
            ],
            'subject' => $quiz->subject,
            'category' => [
                'id' => $category->id,
                'label' => $category->label,
                'grade_from' => $category->grade_from,
                'grade_to' => $category->grade_to,
                'display_range' => $this->formatCategoryRange($category),
            ],
            'questions_count' => $category->questions->count(),
            'attempt_started_at' => optional($requestRecord->attempt_started_at)->toISOString(),
            'remaining_seconds' => $this->remainingSeconds($requestRecord->attempt_started_at, $quiz->time_limit),
            'questions' => $category->questions->values()->map(function ($question) {
                return [
                    'id' => $question->id,
                    'question' => $question->question,
                    'explanation' => $question->explanation,
                    'image' => $question->image,
                    'image_source' => $question->image_source,
                    'position' => $question->position,
                    'answers' => $question->answers->values()->map(
                        fn ($answer, $index) => [
                            'id' => $answer->id,
                            'label' => $answer->label ?: chr(65 + $index),
                            'position' => $answer->position ?: ($index + 1),
                            'answer' => $answer->answer,
                        ]
                    )->values(),
                ];
            })->values(),
        ]);
    }

    public function startAttempt(Request $request, string $quizId)
    {
        $user = Auth::user();
        $quiz = $this->resolveQuiz($quizId, ['categories.questions.answers']);
        $childId = $this->resolveChildId($user->id, $request->input('child_profile_id'));
        $requestRecord = $this->resolveRequest($user->id, $quiz->subject_id, $childId);

        if (!$requestRecord) {
            return response()->json(['message' => 'Р—Р°СЏРІРєР° РЅР° СЌС‚Сѓ РѕР»РёРјРїРёР°РґСѓ РЅРµ РЅР°Р№РґРµРЅР°.'], 404);
        }

        if ($requestRecord->status !== 'approved') {
            return response()->json(['message' => 'Р—Р°СЏРІРєР° РµС‰С‘ РЅРµ РѕРґРѕР±СЂРµРЅР°.'], 403);
        }

        if ($requestRecord->payment_status !== 'paid') {
            return response()->json(['message' => 'РћРїР»Р°С‚Р° РµС‰С‘ РЅРµ РїРѕРґС‚РІРµСЂР¶РґРµРЅР°.'], 402);
        }

        if ($requestRecord->disqualified_at) {
            return response()->json(['message' => 'РџРѕРїС‹С‚РєР° СѓР¶Рµ Р°РЅРЅСѓР»РёСЂРѕРІР°РЅР°.'], 403);
        }

        $exists = QuizResult::query()
            ->where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->when($requestRecord->child_profile_id, fn ($query) => $query->where('child_profile_id', $requestRecord->child_profile_id))
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'РћР»РёРјРїРёР°РґР° СѓР¶Рµ Р±С‹Р»Р° РїСЂРѕР№РґРµРЅР°.'], 403);
        }

        if (!$requestRecord->attempt_started_at) {
            $requestRecord->forceFill([
                'attempt_started_at' => now(),
                'attempt_last_activity_at' => now(),
            ])->save();
        } else {
            $requestRecord->forceFill([
                'attempt_last_activity_at' => now(),
            ])->save();
        }

        return response()->json([
            'started_at' => optional($requestRecord->attempt_started_at)->toISOString(),
            'remaining_seconds' => $this->remainingSeconds($requestRecord->attempt_started_at, $quiz->time_limit),
        ]);
    }

    public function getStatus(Request $request, string $subjectId)
    {
        $user = Auth::user();
        $childId = $this->resolveChildId($user->id, $request->input('child_profile_id'));
        $resolvedSubjectId = $this->resolveSubjectId($subjectId);

        $requestRecord = OlympiadRequest::query()
            ->with(['childProfile', 'paymentRecord'])
            ->where('user_id', $user->id)
            ->where('subject_id', $resolvedSubjectId)
            ->when($childId, fn ($query) => $query->where('child_profile_id', $childId))
            ->latest()
            ->first();

        return response()->json([
            'status' => $requestRecord?->status,
            'payment_status' => $requestRecord?->payment_status,
            'reconciliation_status' => $requestRecord?->paymentRecord?->reconciliation_status ?? 'awaiting_payment',
            'payment_url' => config('services.kaspi.payment_url'),
            'disqualified' => (bool) $requestRecord?->disqualified_at,
            'child_profile_id' => $requestRecord?->childProfile?->public_id,
        ]);
    }

    public function submitQuiz(Request $request, string $quizId)
    {
        $user = Auth::user();

        $request->validate([
            'answers' => 'required|array',
            'child_profile_id' => 'nullable|string',
        ]);

        $quiz = $this->resolveQuiz($quizId, ['categories.questions.answers']);
        $childId = $this->resolveChildId($user->id, $request->input('child_profile_id'));
        $requestRecord = $this->resolveRequest($user->id, $quiz->subject_id, $childId);

        if (!$requestRecord) {
            return response()->json(['message' => 'Заявка на эту олимпиаду не найдена.'], 404);
        }

        if ($requestRecord->status === 'rejected') {
            return response()->json(['message' => 'Попытка аннулирована: заявка отклонена администратором.'], 403);
        }

        if ($requestRecord->status !== 'approved') {
            return response()->json(['message' => 'Заявка ещё не одобрена.'], 403);
        }

        if ($requestRecord->payment_status !== 'paid') {
            return response()->json(['message' => 'Оплата ещё не подтверждена.'], 402);
        }

        if (!$quiz->is_published) {
            return response()->json(['message' => 'Олимпиада пока не опубликована.'], 403);
        }

        if ($requestRecord->disqualified_at) {
            return response()->json(['message' => 'Попытка уже аннулирована.'], 403);
        }

        $exists = QuizResult::query()
            ->where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->when($requestRecord->child_profile_id, fn ($query) => $query->where('child_profile_id', $requestRecord->child_profile_id))
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Олимпиада уже была пройдена.'], 403);
        }

        $category = $this->pickCategoryForGrade($quiz, $requestRecord->grade);

        if (!$category) {
            return response()->json(['message' => 'Категория для этого класса не найдена.'], 422);
        }

        $answers = $request->input('answers', []);
        $score = 0;
        $total = $category->questions->count();
        $answeredCount = collect($answers)->filter(fn ($value) => filled($value))->count();
        $startedAt = $requestRecord->attempt_started_at;
        $submittedAt = now();

        if (!$startedAt) {
            return response()->json([
                'message' => 'РџРѕРїС‹С‚РєР° РЅРµ Р±С‹Р»Р° Р·Р°РїСѓС‰РµРЅР° РЅР° СЃРµСЂРІРµСЂРµ. РќР°С‡РЅРёС‚Рµ РѕР»РёРјРїРёР°РґСѓ Р·Р°РЅРѕРІРѕ.',
            ], 422);
        }

        $elapsedSeconds = max(0, $startedAt->diffInSeconds($submittedAt));
        $timeLimitSeconds = max(60, ((int) $quiz->time_limit) * 60);

        if ($elapsedSeconds > $timeLimitSeconds) {
            $requestRecord->update([
                'disqualified_at' => $requestRecord->disqualified_at ?: $submittedAt,
                'disqualification_reason' => $requestRecord->disqualification_reason ?: 'time_limit_exceeded',
                'attempt_last_activity_at' => $submittedAt,
            ]);

            return response()->json([
                'message' => 'Р’СЂРµРјСЏ РЅР° РѕР»РёРјРїРёР°РґСѓ РёСЃС‚РµРєР»Рѕ. Р РµР·СѓР»СЊС‚Р°С‚ РЅРµ Р·Р°С‡С‚С‘РЅ.',
            ], 403);
        }

        $reviewReasons = $this->buildReviewReasons(
            elapsedSeconds: $elapsedSeconds,
            answeredCount: $answeredCount,
            totalQuestions: $total,
            timeLimitSeconds: $timeLimitSeconds,
        );

        foreach ($category->questions as $question) {
            if (!isset($answers[$question->id])) {
                continue;
            }

            $correct = $question->answers->firstWhere('is_correct', true);

            if ($correct && (int) $correct->id === (int) $answers[$question->id]) {
                $score++;
            }
        }

        $quizResult = QuizResult::create([
            'user_id' => $user->id,
            'child_profile_id' => $requestRecord->child_profile_id,
            'quiz_id' => $quiz->id,
            'quiz_category_id' => $category->id,
            'score' => $score,
            'total' => $total,
            'answers' => $answers,
            'started_at' => $startedAt,
            'submitted_at' => $submittedAt,
            'elapsed_seconds' => $elapsedSeconds,
            'requires_review' => !empty($reviewReasons),
            'review_reasons' => $reviewReasons ?: null,
        ]);

        OlympiadRequest::query()
            ->where('user_id', $user->id)
            ->where('subject_id', $quiz->subject_id)
            ->when($requestRecord->child_profile_id, fn ($query) => $query->where('child_profile_id', $requestRecord->child_profile_id))
            ->update([
                'completed' => true,
                'attempt_last_activity_at' => $submittedAt,
            ]);

        $percent = $total > 0 ? (int) round(($score / $total) * 100) : 0;
        $resultStatus = $percent >= 60 ? 'passed' : 'failed';

        OnboardingProgress::syncStep($user, 'results_certificate');

        NotificationWorkflow::createForUser(
            user: $user,
            type: 'result_published',
            title: 'Результат опубликован',
            body: "Результат по олимпиаде {$quiz->subject?->name} уже доступен в кабинете. Итог: {$score} из {$total}.",
            actionUrl: rtrim(config('app.url'), '/') . '/results',
            statusKey: $resultStatus,
            payload: [
                'action_label' => 'Смотреть результат',
                'context' => [
                    'Предмет' => $quiz->subject?->name ?? 'Олимпиада',
                    'Результат' => "{$score} из {$total}",
                ],
            ],
            sendEmail: true
        );

        NotificationWorkflow::createForUser(
            user: $user,
            type: 'certificate_available',
            title: 'Сертификат готов',
            body: "Сертификат по олимпиаде {$quiz->subject?->name} можно открыть и скачать из кабинета.",
            actionUrl: rtrim(config('app.url'), '/') . '/results',
            statusKey: 'certificate_available',
            payload: [
                'action_label' => 'Открыть сертификат',
            ],
            sendEmail: true
        );

        return response()->json([
            'message' => 'Олимпиада завершена.',
            'id' => $quizResult->public_id,
            'score' => $score,
            'total' => $total,
            'percent' => $percent,
            'status' => $resultStatus,
            'elapsed_seconds' => $elapsedSeconds,
            'requires_review' => !empty($reviewReasons),
            'review_reasons' => $reviewReasons,
            'category' => [
                'id' => $category->id,
                'label' => $category->label,
                'display_range' => $this->formatCategoryRange($category),
            ],
            'certificate_url' => '/api/profile/results/' . $quizResult->public_id . '/certificate',
            'mistakes_url' => '/profile/results/' . $quizResult->public_id . '/mistakes',
        ]);
    }

    public function violateAttempt(Request $request, string $quizId)
    {
        $user = Auth::user();
        $quiz = $this->resolveQuiz($quizId);
        $childId = $this->resolveChildId($user->id, $request->input('child_profile_id'));
        $requestRecord = $this->resolveRequest($user->id, $quiz->subject_id, $childId);

        if (!$requestRecord) {
            return response()->json(['message' => 'Попытка не найдена.'], 404);
        }

        if (!$requestRecord->disqualified_at) {
            $requestRecord->update([
                'disqualified_at' => now(),
                'disqualification_reason' => $request->input('reason', 'window_focus_lost'),
                'attempt_last_activity_at' => now(),
            ]);
        }

        return response()->json(['message' => 'Попытка аннулирована.']);
    }

    protected function resolveRequest(int $userId, int $subjectId, ?int $childId = null): ?OlympiadRequest
    {
        return OlympiadRequest::with(['childProfile', 'paymentRecord'])
            ->where('user_id', $userId)
            ->where('subject_id', $subjectId)
            ->when($childId, fn ($query) => $query->where('child_profile_id', $childId))
            ->latest()
            ->first();
    }

    protected function pickCategoryForGrade(Quiz $quiz, mixed $grade): ?QuizCategory
    {
        $gradeNumber = $this->normalizeGrade($grade);

        if ($gradeNumber !== null) {
            $matched = $quiz->categories
                ->sortBy('sort_order')
                ->first(function (QuizCategory $category) use ($gradeNumber) {
                    if ($category->grade_from === null || $category->grade_to === null) {
                        return false;
                    }

                    return $gradeNumber >= $category->grade_from && $gradeNumber <= $category->grade_to;
                });

            if ($matched) {
                return $matched;
            }
        }

        return $quiz->categories
            ->sortBy('sort_order')
            ->first(fn (QuizCategory $category) => $category->grade_from === null && $category->grade_to === null);
    }

    protected function normalizeGrade(mixed $grade): ?int
    {
        if (is_numeric($grade)) {
            return (int) $grade;
        }

        if (!is_string($grade)) {
            return null;
        }

        preg_match('/\d+/', $grade, $matches);

        return isset($matches[0]) ? (int) $matches[0] : null;
    }

    protected function formatCategoryRange(QuizCategory $category): string
    {
        if ($category->grade_from === null || $category->grade_to === null) {
            return $category->label;
        }

        return $category->grade_from === $category->grade_to
            ? (string) $category->grade_from
            : "{$category->grade_from}-{$category->grade_to}";
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

    protected function resolveQuiz(string $quizKey, array $with = []): Quiz
    {
        $query = Quiz::query()->with($with);

        return $query
            ->where('public_id', $quizKey)
            ->firstOrFail();
    }

    protected function remainingSeconds($startedAt, ?int $timeLimitMinutes): int
    {
        $timeLimitSeconds = max(60, ((int) $timeLimitMinutes) * 60);

        if (!$startedAt) {
            return $timeLimitSeconds;
        }

        return max(0, $timeLimitSeconds - $startedAt->diffInSeconds(now()));
    }

    protected function buildReviewReasons(int $elapsedSeconds, int $answeredCount, int $totalQuestions, int $timeLimitSeconds): array
    {
        $reasons = [];
        $minimumReasonableElapsed = max(15, min(90, $answeredCount * 3));

        if ($answeredCount >= 3 && $elapsedSeconds < $minimumReasonableElapsed) {
            $reasons[] = 'answered_too_fast';
        }

        if ($elapsedSeconds < max(10, (int) floor($timeLimitSeconds * 0.05)) && $totalQuestions >= 10) {
            $reasons[] = 'attempt_shorter_than_expected';
        }

        return array_values(array_unique($reasons));
    }
}
