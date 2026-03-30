<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OlympiadRequest;
use App\Models\Quiz;
use App\Models\QuizCategory;
use App\Models\QuizResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizController extends Controller
{
    public function getSubjects()
    {
        $user = Auth::user();

        $approvedSubjects = OlympiadRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('payment_status', 'paid')
            ->pluck('subject_id');

        return response()->json(
            Quiz::with('subject')
                ->withCount('questions')
                ->where('is_published', true)
                ->whereIn('subject_id', $approvedSubjects)
                ->get()
        );
    }

    public function getQuiz($subjectId)
    {
        $user = Auth::user();
        $requestRecord = $this->resolveApprovedRequest($user->id, $subjectId);

        if (!$requestRecord) {
            return response()->json(['message' => 'Р’С‹ РЅРµ РґРѕРїСѓС‰РµРЅС‹ Рє РѕР»РёРјРїРёР°РґРµ'], 403);
        }

        if ($requestRecord->disqualified_at) {
            return response()->json(['message' => 'РџРѕРїС‹С‚РєР° Р°РЅРЅСѓР»РёСЂРѕРІР°РЅР° РёР·-Р·Р° РЅР°СЂСѓС€РµРЅРёСЏ РїСЂР°РІРёР» С‚РµСЃС‚РёСЂРѕРІР°РЅРёСЏ.'], 403);
        }

        if ($requestRecord->payment_status !== 'paid') {
            return response()->json([
                'message' => 'Р”Р»СЏ РЅР°С‡Р°Р»Р° С‚РµСЃС‚Р° РЅСѓР¶РЅРѕ РїРѕРґС‚РІРµСЂРґРёС‚СЊ РѕРїР»Р°С‚Сѓ.',
                'payment_required' => true,
                'payment_status' => $requestRecord->payment_status,
                'payment_url' => config('services.kaspi.payment_url'),
            ], 402);
        }

        $quiz = Quiz::where('subject_id', $subjectId)
            ->where('is_published', true)
            ->with([
                'subject',
                'categories.questions.answers:id,question_id,label,position,answer,is_correct',
            ])
            ->first();

        if (!$quiz) {
            return response()->json(['message' => 'РўРµСЃС‚ РЅРµ РЅР°Р№РґРµРЅ'], 404);
        }

        $alreadySubmitted = QuizResult::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->exists();

        $category = $this->pickCategoryForGrade($quiz, $requestRecord->grade);

        if (!$category) {
            return response()->json([
                'message' => 'Р”Р»СЏ СѓРєР°Р·Р°РЅРЅРѕРіРѕ РєР»Р°СЃСЃР° РїРѕРєР° РЅРµ РЅР°СЃС‚СЂРѕРµРЅР° РєР°С‚РµРіРѕСЂРёСЏ СЌС‚РѕР№ РѕР»РёРјРїРёР°РґС‹.',
            ], 422);
        }

        return response()->json([
            'id' => $quiz->id,
            'subject_id' => $quiz->subject_id,
            'title' => $quiz->title,
            'description' => $quiz->description,
            'time_limit' => $quiz->time_limit,
            'already_submitted' => $alreadySubmitted,
            'warning' => 'Р’Рѕ РІСЂРµРјСЏ С‚РµСЃС‚Р° РЅРµР»СЊР·СЏ РїРµСЂРµРєР»СЋС‡Р°С‚СЊ РІРєР»Р°РґРєСѓ, РѕРєРЅРѕ, РІС‹С…РѕРґРёС‚СЊ РёР· fullscreen РёР»Рё СЃРІРѕСЂР°С‡РёРІР°С‚СЊ Р±СЂР°СѓР·РµСЂ.',
            'warning_rules' => [
                'Р—Р°РїСЂРµС‰РµРЅРѕ РїРµСЂРµРєР»СЋС‡Р°С‚СЊ РІРєР»Р°РґРєСѓ, РѕРєРЅРѕ, РІС‹С…РѕРґРёС‚СЊ РёР· fullscreen РёР»Рё СЃРІРѕСЂР°С‡РёРІР°С‚СЊ Р±СЂР°СѓР·РµСЂ.',
                'РќРµР»СЊР·СЏ РёСЃРїРѕР»СЊР·РѕРІР°С‚СЊ РїРѕРґСЃРєР°Р·РєРё, СЃРїРёСЃС‹РІР°С‚СЊ Рё РѕР±СЂР°С‰Р°С‚СЊСЃСЏ Рє СЃС‚РѕСЂРѕРЅРЅРµР№ РїРѕРјРѕС‰Рё.',
                'РџСЂРё РЅР°СЂСѓС€РµРЅРёРё РїСЂР°РІРёР» РїРѕРїС‹С‚РєР° Р°РЅРЅСѓР»РёСЂСѓРµС‚СЃСЏ Р°РІС‚РѕРјР°С‚РёС‡РµСЃРєРё.',
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
            'questions' => $category->questions->values()->map(function ($question) {
                return [
                    'id' => $question->id,
                    'question' => $question->question,
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

    public function getStatus($subjectId)
    {
        $user = Auth::user();

        $requestRecord = OlympiadRequest::where('user_id', $user->id)
            ->where('subject_id', $subjectId)
            ->latest()
            ->first();

        return response()->json([
            'status' => $requestRecord?->status,
            'payment_status' => $requestRecord?->payment_status,
            'payment_url' => config('services.kaspi.payment_url'),
            'disqualified' => (bool) $requestRecord?->disqualified_at,
        ]);
    }

    public function submitQuiz(Request $request, $quizId)
    {
        $user = Auth::user();

        $request->validate([
            'answers' => 'required|array',
        ]);

        $exists = QuizResult::where('user_id', $user->id)
            ->where('quiz_id', $quizId)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'РўРµСЃС‚ СѓР¶Рµ Р±С‹Р» РїСЂРѕР№РґРµРЅ'], 403);
        }

        $quiz = Quiz::with(['categories.questions.answers'])->findOrFail($quizId);

        if (!$quiz->is_published) {
            return response()->json(['message' => 'РўРµСЃС‚ РїРѕРєР° РЅРµ РѕРїСѓР±Р»РёРєРѕРІР°РЅ'], 403);
        }

        $requestRecord = $this->resolveApprovedRequest($user->id, $quiz->subject_id);

        if (!$requestRecord) {
            return response()->json(['message' => 'Р’С‹ РЅРµ РґРѕРїСѓС‰РµРЅС‹ Рє РѕР»РёРјРїРёР°РґРµ'], 403);
        }

        if ($requestRecord->disqualified_at) {
            return response()->json(['message' => 'РџРѕРїС‹С‚РєР° СѓР¶Рµ Р°РЅРЅСѓР»РёСЂРѕРІР°РЅР°.'], 403);
        }

        if ($requestRecord->payment_status !== 'paid') {
            return response()->json(['message' => 'РћРїР»Р°С‚Р° РµС‰С‘ РЅРµ РїРѕРґС‚РІРµСЂР¶РґРµРЅР°.'], 402);
        }

        $category = $this->pickCategoryForGrade($quiz, $requestRecord->grade);

        if (!$category) {
            return response()->json(['message' => 'РљР°С‚РµРіРѕСЂРёСЏ РґР»СЏ СЌС‚РѕРіРѕ РєР»Р°СЃСЃР° РЅРµ РЅР°Р№РґРµРЅР°.'], 422);
        }

        $answers = $request->input('answers', []);
        $score = 0;
        $total = $category->questions->count();

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
            'quiz_id' => $quizId,
            'quiz_category_id' => $category->id,
            'score' => $score,
            'total' => $total,
        ]);

        OlympiadRequest::where('user_id', $user->id)
            ->where('subject_id', $quiz->subject_id)
            ->update(['completed' => true]);

        $percent = $total > 0 ? (int) round(($score / $total) * 100) : 0;

        return response()->json([
            'message' => 'РўРµСЃС‚ Р·Р°РІРµСЂС€РµРЅ',
            'id' => $quizResult->id,
            'score' => $score,
            'total' => $total,
            'percent' => $percent,
            'status' => $percent >= 60 ? 'passed' : 'failed',
            'category' => [
                'id' => $category->id,
                'label' => $category->label,
                'display_range' => $this->formatCategoryRange($category),
            ],
            'certificate_url' => '/api/profile/results/' . $quizResult->id . '/certificate',
        ]);
    }

    public function violateAttempt(Request $request, $quizId)
    {
        $user = Auth::user();
        $quiz = Quiz::findOrFail($quizId);
        $requestRecord = $this->resolveApprovedRequest($user->id, $quiz->subject_id);

        if (!$requestRecord) {
            return response()->json(['message' => 'РџРѕРїС‹С‚РєР° РЅРµ РЅР°Р№РґРµРЅР°.'], 404);
        }

        if (!$requestRecord->disqualified_at) {
            $requestRecord->update([
                'disqualified_at' => now(),
                'disqualification_reason' => $request->input('reason', 'window_focus_lost'),
            ]);
        }

        return response()->json(['message' => 'РџРѕРїС‹С‚РєР° Р°РЅРЅСѓР»РёСЂРѕРІР°РЅР°.']);
    }

    protected function resolveApprovedRequest(int $userId, int $subjectId): ?OlympiadRequest
    {
        $requestRecord = OlympiadRequest::where('user_id', $userId)
            ->where('subject_id', $subjectId)
            ->latest()
            ->first();

        if (!$requestRecord || $requestRecord->status !== 'approved') {
            return null;
        }

        return $requestRecord;
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
}
