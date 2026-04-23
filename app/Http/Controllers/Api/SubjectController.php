<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizCategory;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        try {
            return response()->json(
                Subject::whereHas('quizzes', fn ($query) => $query->where('is_published', true))
                    ->withCount([
                        'quizzes as published_quizzes_count' => fn ($query) => $query->where('is_published', true),
                    ])
                    ->with(['quizzes' => fn ($q) => $q->where('is_published', true)->orderByDesc('id')])
                    ->orderBy('name')
                    ->get()
                    ->map(fn (Subject $subject) => $this->mapSubject($subject))
            );
        } catch (\Exception) {
            return response()->json([
                'message' => 'Ошибка загрузки предметов.',
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $subject = Subject::query()
                ->where('public_id', $id)
                ->whereHas('quizzes', fn ($query) => $query->where('is_published', true))
                ->with([
                    'quizzes' => fn ($query) => $query
                        ->where('is_published', true)
                        ->with('categories')
                        ->orderByDesc('id'),
                ])
                ->first();

            if (!$subject) {
                return response()->json([
                    'message' => 'Предмет не найден.',
                ], 404);
            }

            return response()->json($this->mapSubjectDetail($subject));
        } catch (\Exception) {
            return response()->json([
                'message' => 'Ошибка сервера.',
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'start_date' => 'required|date',
        ]);

        try {
            $subject = Subject::create($request->only('name', 'description', 'image', 'start_date'));

            return response()->json($this->mapSubject($subject), 201);
        } catch (\Exception) {
            return response()->json([
                'message' => 'Ошибка создания предмета.',
            ], 500);
        }
    }

    protected function mapSubject(Subject $subject): array
    {
        return [
            'id' => $subject->public_id,
            'name' => $subject->name,
            'image' => $subject->image,
            'description' => $subject->description,
            'start_date' => optional($subject->start_date)->toDateString(),
            'published_quizzes_count' => $subject->published_quizzes_count ?? 0,
            'price' => $subject->relationLoaded('quizzes')
                ? $subject->quizzes->first()?->price
                : $subject->quizzes()->where('is_published', true)->latest('id')->value('price'),
        ];
    }

    protected function mapSubjectDetail(Subject $subject): array
    {
        /** @var Quiz|null $quiz */
        $quiz = $subject->quizzes->first();
        $gradeRanges = $quiz
            ? $quiz->categories
                ->map(function (QuizCategory $category) {
                    if ($category->grade_from === null || $category->grade_to === null) {
                        return null;
                    }

                    return $category->grade_from === $category->grade_to
                        ? (string) $category->grade_from
                        : "{$category->grade_from}-{$category->grade_to}";
                })
                ->filter()
                ->unique()
                ->values()
                ->all()
            : [];

        return [
            ...$this->mapSubject($subject),
            'time_limit' => $quiz?->time_limit,
            'price' => $quiz?->price,
            'grade_ranges' => $gradeRanges,
            'registration_url' => '/subject?subject=' . $subject->public_id,
            'payment_url' => config('services.kaspi.payment_url'),
            'faq' => [
                [
                    'question' => "Как зарегистрироваться на олимпиаду по {$subject->name}?",
                    'answer' => 'Выберите предмет, заполните данные участника, оплатите участие через Kaspi и дождитесь подтверждения платежа.',
                ],
                [
                    'question' => 'Для каких классов подходит олимпиада?',
                    'answer' => 'Актуальные диапазоны классов указаны на странице предмета и зависят от опубликованных категорий олимпиады.',
                ],
                [
                    'question' => 'Когда будут результаты?',
                    'answer' => 'Результаты доступны после завершения олимпиады в личном кабинете, а сертификат можно проверить по публичному ID.',
                ],
            ],
        ];
    }
}
