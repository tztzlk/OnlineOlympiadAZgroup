<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChildProfile;
use App\Models\Quiz;
use App\Models\QuizCategory;
use App\Models\Subject;
use App\Models\TrainingAttempt;
use Illuminate\Http\Request;

class TrainingController extends Controller
{
    public function getQuiz(Request $request, string $subjectId)
    {
        $user = $request->user();
        $child = $this->resolveChildProfile($request, $user->id);

        $quiz = Quiz::query()
            ->where('subject_id', $this->resolveSubjectId($subjectId))
            ->where('is_published', true)
            ->with([
                'subject',
                'categories.questions.answers:id,question_id,label,position,answer,is_correct',
            ])
            ->firstOrFail();

        $category = $this->pickCategoryForGrade($quiz, $child->grade);

        if (!$category) {
            return response()->json([
                'message' => 'Для указанного класса пока нет тренировочных заданий.',
            ], 422);
        }

        return response()->json([
            'id' => $quiz->public_id,
            'subject_id' => $quiz->subject?->public_id,
            'title' => $quiz->title,
            'description' => $quiz->description,
            'time_limit' => $quiz->time_limit,
            'mode' => 'training',
            'subject' => $quiz->subject,
            'child' => [
                'id' => $child->public_id,
                'full_name' => $child->full_name,
                'grade' => $child->grade,
            ],
            'category' => [
                'id' => $category->id,
                'label' => $category->label,
                'display_range' => $this->formatCategoryRange($category),
            ],
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

    public function submit(Request $request, string $quizId)
    {
        $user = $request->user();
        $child = $this->resolveChildProfile($request, $user->id);

        $request->validate([
            'answers' => 'required|array',
        ]);

        $quiz = $this->resolveQuiz($quizId);
        $category = $this->pickCategoryForGrade($quiz, $child->grade);

        if (!$category) {
            return response()->json([
                'message' => 'Категория для тренировки не найдена.',
            ], 422);
        }

        $answers = $request->input('answers', []);
        $score = 0;
        $items = [];

        foreach ($category->questions as $question) {
            $selectedId = isset($answers[$question->id]) ? (int) $answers[$question->id] : null;
            $correct = $question->answers->firstWhere('is_correct', true);
            $isCorrect = $correct && $selectedId === (int) $correct->id;

            if ($isCorrect) {
                $score++;
            }

            $items[] = [
                'question_id' => $question->id,
                'question' => $question->question,
                'selected_answer_id' => $selectedId,
                'correct_answer_id' => $correct?->id,
                'correct_answer' => $correct?->answer,
                'is_correct' => (bool) $isCorrect,
                'explanation' => 'Правильный ответ: ' . ($correct?->answer ?? 'не найден'),
            ];
        }

        $attempt = TrainingAttempt::create([
            'parent_id' => $user->id,
            'child_profile_id' => $child->id,
            'quiz_id' => $quiz->id,
            'quiz_category_id' => $category->id,
            'score' => $score,
            'total' => count($items),
            'answers' => $answers,
            'completed_at' => now(),
        ]);

        return response()->json([
            'attempt_id' => $attempt->id,
            'score' => $score,
            'total' => count($items),
            'percent' => count($items) > 0 ? (int) round(($score / count($items)) * 100) : 0,
            'child' => [
                'id' => $child->public_id,
                'full_name' => $child->full_name,
            ],
            'items' => $items,
        ]);
    }

    protected function resolveChildProfile(Request $request, int $parentId): ChildProfile
    {
        $childPublicId = $request->input('child_profile_id') ?: $request->query('child_profile_id');

        $child = ChildProfile::query()
            ->where('parent_id', $parentId)
            ->when($childPublicId, fn ($query) => $query->where('public_id', (string) $childPublicId))
            ->orderBy('id')
            ->first();

        if (!$child) {
            abort(422, 'Сначала создайте профиль ребёнка.');
        }

        return $child;
    }

    protected function pickCategoryForGrade(Quiz $quiz, mixed $grade): ?QuizCategory
    {
        $gradeNumber = is_numeric($grade) ? (int) $grade : null;

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

    protected function formatCategoryRange(QuizCategory $category): string
    {
        if ($category->grade_from === null || $category->grade_to === null) {
            return $category->label;
        }

        return $category->grade_from === $category->grade_to
            ? (string) $category->grade_from
            : "{$category->grade_from}-{$category->grade_to}";
    }

    protected function resolveSubjectId(string $subjectKey): int
    {
        return Subject::query()
            ->where('public_id', $subjectKey)
            ->valueOrFail('id');
    }

    protected function resolveQuiz(string $quizKey): Quiz
    {
        return Quiz::query()
            ->with(['categories.questions.answers'])
            ->where('public_id', $quizKey)
            ->firstOrFail();
    }
}
