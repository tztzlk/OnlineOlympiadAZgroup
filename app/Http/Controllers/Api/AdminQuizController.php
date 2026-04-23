<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizCategory;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminQuizController extends Controller
{
    public function index()
    {
        return response()->json(
            Quiz::with(['subject', 'categories'])
                ->withCount('questions')
                ->latest()
                ->get()
                ->map(fn (Quiz $quiz) => $this->mapQuizSummary($quiz))
        );
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);
        $subject = $this->resolveSubject($data);

        $quiz = DB::transaction(function () use ($data, $subject) {
            $quiz = Quiz::create([
                'subject_id' => $subject->id,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'price' => $data['price'],
                'time_limit' => $data['time_limit'],
                'is_published' => (bool) ($data['is_published'] ?? false),
            ]);

            $this->syncCategories($quiz, $data['categories']);

            return $quiz;
        });

        return response()->json($this->loadQuizDetails($quiz), 201);
    }

    public function show(Quiz $quiz)
    {
        return response()->json($this->loadQuizDetails($quiz));
    }

    public function update(Request $request, Quiz $quiz)
    {
        $data = $this->validatePayload($request);
        $subject = $this->resolveSubject($data);

        DB::transaction(function () use ($data, $subject, $quiz) {
            $quiz->update([
                'subject_id' => $subject->id,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'price' => $data['price'],
                'time_limit' => $data['time_limit'],
                'is_published' => (bool) ($data['is_published'] ?? false),
            ]);

            $this->syncCategories($quiz, $data['categories']);
        });

        return response()->json($this->loadQuizDetails($quiz->fresh()));
    }

    public function destroy(Quiz $quiz)
    {
        $quiz->delete();

        return response()->json([
            'message' => 'Олимпиада удалена.',
        ]);
    }

    public function publish(Quiz $quiz)
    {
        $quiz->update(['is_published' => true]);

        return response()->json([
            'message' => 'Олимпиада опубликована.',
            'quiz' => $this->mapQuizSummary($quiz->fresh()->load(['subject', 'categories'])->loadCount('questions')),
        ]);
    }

    public function unpublish(Quiz $quiz)
    {
        $quiz->update(['is_published' => false]);

        return response()->json([
            'message' => 'Публикация олимпиады снята.',
            'quiz' => $this->mapQuizSummary($quiz->fresh()->load(['subject', 'categories'])->loadCount('questions')),
        ]);
    }

    public function uploadQuestionImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:4096',
        ]);

        $path = $request->file('image')->store('question-images', 'public');

        return response()->json([
            'path' => $path,
            'url' => asset('storage/' . $path),
        ]);
    }

    public function uploadSubjectImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:4096',
        ]);

        $path = $request->file('image')->store('subject-images', 'public');

        return response()->json([
            'path' => $path,
            'url' => asset('storage/' . $path),
        ]);
    }

    protected function validatePayload(Request $request): array
    {
        $data = $request->validate([
            'subject_id' => 'nullable|string',
            'subject.name' => 'required_without:subject_id|string|max:255',
            'subject.description' => 'nullable|string',
            'subject.image' => 'nullable|string',
            'subject.start_date' => 'nullable|date',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|integer|min:0|max:10000000',
            'time_limit' => 'required|integer|min:1|max:180',
            'is_published' => 'sometimes|boolean',
            'categories' => 'required|array|min:1|max:10',
            'categories.*.label' => 'required|string|max:50',
            'categories.*.grade_from' => 'nullable|integer|min:1|max:11',
            'categories.*.grade_to' => 'nullable|integer|min:1|max:11',
            'categories.*.sort_order' => 'nullable|integer|min:1|max:20',
            'categories.*.questions' => 'required|array|min:1|max:100',
            'categories.*.questions.*.question' => 'required|string',
            'categories.*.questions.*.explanation' => 'nullable|string',
            'categories.*.questions.*.image_source' => 'nullable|string|in:url,upload',
            'categories.*.questions.*.image_url' => 'nullable|string',
            'categories.*.questions.*.image_path' => 'nullable|string',
            'categories.*.questions.*.position' => 'nullable|integer|min:1|max:100',
            'categories.*.questions.*.answers' => 'required|array|min:2|max:8',
            'categories.*.questions.*.correct_answer' => 'required|string|max:10',
            'categories.*.questions.*.answers.*.label' => 'required|string|max:10',
            'categories.*.questions.*.answers.*.answer' => 'required|string',
            'categories.*.questions.*.answers.*.position' => 'nullable|integer|min:1|max:8',
        ]);

        $data['categories'] = collect($data['categories'])
            ->filter(fn (array $category) => !empty($category['questions']))
            ->values()
            ->all();

        if (count($data['categories']) === 0) {
            abort(response()->json([
                'message' => 'At least one category with questions is required.',
            ], 422));
        }

        foreach ($data['categories'] as $category) {
            foreach ($category['questions'] as $questionIndex => $question) {
                $labels = collect($question['answers'])->pluck('label')->map(fn ($label) => trim((string) $label));

                if ($labels->contains('')) {
                    abort(response()->json([
                        'message' => "Question " . ($questionIndex + 1) . " in category {$category['label']} contains an empty answer label.",
                    ], 422));
                }

                if ($labels->duplicates()->isNotEmpty()) {
                    abort(response()->json([
                        'message' => "Question " . ($questionIndex + 1) . " in category {$category['label']} has duplicate answer labels.",
                    ], 422));
                }

                if (!$labels->contains((string) $question['correct_answer'])) {
                    abort(response()->json([
                        'message' => "Question " . ($questionIndex + 1) . " in category {$category['label']} must point to an existing correct answer.",
                    ], 422));
                }
            }
        }

        return $data;
    }

    protected function resolveSubject(array $data): Subject
    {
        if (!empty($data['subject_id'])) {
            return Subject::query()
                ->where('public_id', $data['subject_id'])
                ->firstOrFail();
        }

        return Subject::create([
            'name' => $data['subject']['name'],
            'description' => $data['subject']['description'] ?? null,
            'image' => $data['subject']['image'] ?? null,
            'start_date' => $data['subject']['start_date'] ?? now()->toDateString(),
        ]);
    }

    protected function syncCategories(Quiz $quiz, array $categories): void
    {
        $quiz->categories()->delete();
        $quiz->questions()->delete();

        foreach ($categories as $categoryIndex => $categoryData) {
            $category = $quiz->categories()->create([
                'label' => $categoryData['label'],
                'grade_from' => $categoryData['grade_from'] ?? null,
                'grade_to' => $categoryData['grade_to'] ?? null,
                'sort_order' => $categoryData['sort_order'] ?? ($categoryIndex + 1),
            ]);

            foreach ($categoryData['questions'] as $questionIndex => $questionData) {
                $imageSource = $questionData['image_source'] ?? null;

                $question = $quiz->questions()->create([
                    'quiz_category_id' => $category->id,
                    'question' => $questionData['question'],
                    'explanation' => $questionData['explanation'] ?? null,
                    'image_source' => $imageSource,
                    'image_url' => $imageSource === 'url' ? ($questionData['image_url'] ?? null) : null,
                    'image_path' => $imageSource === 'upload' ? ($questionData['image_path'] ?? null) : null,
                    'position' => $questionData['position'] ?? ($questionIndex + 1),
                ]);

                foreach ($questionData['answers'] as $answerIndex => $answerData) {
                    $question->answers()->create([
                        'label' => $answerData['label'],
                        'position' => $answerData['position'] ?? ($answerIndex + 1),
                        'answer' => $answerData['answer'],
                        'is_correct' => $answerData['label'] === $questionData['correct_answer'],
                    ]);
                }
            }
        }
    }

    protected function loadQuizDetails(Quiz $quiz): array
    {
        $quiz->load([
            'subject',
            'categories.questions.answers',
        ])->loadCount('questions');

        return [
            'id' => $quiz->public_id,
            'title' => $quiz->title,
            'description' => $quiz->description,
            'price' => $quiz->price,
            'time_limit' => $quiz->time_limit,
            'is_published' => $quiz->is_published,
            'questions_count' => $quiz->questions_count,
            'subject' => $quiz->subject,
            'categories' => $quiz->categories->map(function (QuizCategory $category) {
                return [
                    'id' => $category->id,
                    'label' => $category->label,
                    'grade_from' => $category->grade_from,
                    'grade_to' => $category->grade_to,
                    'sort_order' => $category->sort_order,
                    'questions' => $category->questions->map(function ($question) {
                        return [
                            'id' => $question->id,
                            'question' => $question->question,
                            'explanation' => $question->explanation,
                            'image_source' => $question->image_source,
                            'image_url' => $question->image_url,
                            'image_path' => $question->image_path,
                            'image' => $question->image,
                            'position' => $question->position,
                            'correct_answer' => $question->answers->firstWhere('is_correct', true)?->label ?? 'A',
                            'answers' => $question->answers->map(function ($answer, $index) {
                                return [
                                    'id' => $answer->id,
                                    'label' => $answer->label ?: chr(65 + $index),
                                    'position' => $answer->position ?? ($index + 1),
                                    'answer' => $answer->answer,
                                    'is_correct' => (bool) $answer->is_correct,
                                ];
                            })->values(),
                        ];
                    })->values(),
                ];
            })->values(),
        ];
    }

    protected function mapQuizSummary(Quiz $quiz): array
    {
        return [
            'id' => $quiz->public_id,
            'title' => $quiz->title,
            'description' => $quiz->description,
            'price' => $quiz->price,
            'time_limit' => $quiz->time_limit,
            'is_published' => $quiz->is_published,
            'questions_count' => $quiz->questions_count,
            'subject' => $quiz->subject,
            'categories' => $quiz->categories->map(fn (QuizCategory $category) => [
                'id' => $category->id,
                'label' => $category->label,
                'grade_from' => $category->grade_from,
                'grade_to' => $category->grade_to,
            ])->values(),
        ];
    }
}

