<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QuizExportImportController extends Controller
{
    public function export(Quiz $quiz)
    {
        $quiz->load(['subject', 'categories.questions.answers']);

        $subjectImageData = null;
        $subjectImageExt  = null;
        if ($quiz->subject?->image) {
            $path = $quiz->subject->image;
            // image may be stored as full URL or as a storage path
            if (filter_var($path, FILTER_VALIDATE_URL)) {
                $subjectImageData = null; // external URL — no blob to export
            } elseif (Storage::disk('public')->exists($path)) {
                $blob             = Storage::disk('public')->get($path);
                $subjectImageData = base64_encode($blob);
                $subjectImageExt  = pathinfo($path, PATHINFO_EXTENSION) ?: 'jpg';
            }
        }

        $categories = $quiz->categories->map(function ($category) {
            return [
                'label'      => $category->label,
                'grade_from' => $category->grade_from,
                'grade_to'   => $category->grade_to,
                'sort_order' => $category->sort_order,
                'questions'  => $category->questions->map(function ($question) {
                    $imageData = null;
                    $imageExt  = null;

                    if ($question->image_source === 'upload' && $question->image_path) {
                        if (Storage::disk('public')->exists($question->image_path)) {
                            $blob      = Storage::disk('public')->get($question->image_path);
                            $imageData = base64_encode($blob);
                            $imageExt  = pathinfo($question->image_path, PATHINFO_EXTENSION) ?: 'jpg';
                        }
                    }

                    $correctAnswer = $question->answers->firstWhere('is_correct', true)?->label ?? 'A';

                    return [
                        'question'      => $question->question,
                        'explanation'   => $question->explanation ?? '',
                        'position'      => $question->position,
                        'correct_answer' => $correctAnswer,
                        'image_source'  => $question->image_source,
                        'image_url'     => $question->image_source === 'url' ? $question->image_url : null,
                        'image_data'    => $imageData,
                        'image_ext'     => $imageExt,
                        'answers'       => $question->answers->map(fn ($a, $i) => [
                            'label'    => $a->label ?: chr(65 + $i),
                            'position' => $a->position ?? ($i + 1),
                            'answer'   => $a->answer,
                        ])->values(),
                    ];
                })->values(),
            ];
        })->values();

        $payload = [
            '_version'     => 1,
            '_exported_at' => now()->toIso8601String(),
            'quiz' => [
                'title'       => $quiz->title,
                'description' => $quiz->description ?? '',
                'price'       => $quiz->price,
                'time_limit'  => $quiz->time_limit,
                'is_published' => false, // always export as draft
                'subject'     => [
                    'name'        => $quiz->subject?->name ?? '',
                    'description' => $quiz->subject?->description ?? '',
                    'start_date'  => $quiz->subject?->start_date,
                    'end_date'    => $quiz->subject?->end_date,
                    'image_url'   => filter_var($quiz->subject?->image ?? '', FILTER_VALIDATE_URL)
                                        ? $quiz->subject->image
                                        : null,
                    'image_data'  => $subjectImageData,
                    'image_ext'   => $subjectImageExt,
                ],
                'categories' => $categories,
            ],
        ];

        $filename = Str::slug($quiz->title ?: 'quiz') . '-' . now()->format('Ymd-His') . '.json';
        $json     = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return response($json, 200, [
            'Content-Type'        => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function importJson(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:json|max:51200',
        ]);

        $raw = file_get_contents($request->file('file')->getRealPath());
        $data = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE || empty($data['quiz'])) {
            return response()->json(['message' => 'Невалидный JSON-файл.'], 422);
        }

        $version = $data['_version'] ?? 1;
        if ($version !== 1) {
            return response()->json(['message' => 'Неподдерживаемая версия формата экспорта.'], 422);
        }

        $q = $data['quiz'];

        if (empty($q['title']) || empty($q['categories'])) {
            return response()->json(['message' => 'Файл не содержит необходимых данных (title, categories).'], 422);
        }

        try {
            $quiz = DB::transaction(function () use ($q) {
                $subject = $this->resolveSubjectFromImport($q['subject'] ?? []);

                $quiz = Quiz::create([
                    'subject_id'   => $subject->id,
                    'title'        => $q['title'],
                    'description'  => $q['description'] ?? null,
                    'price'        => (int) ($q['price'] ?? 2000),
                    'time_limit'   => (int) ($q['time_limit'] ?? 60),
                    'is_published' => false,
                ]);

                foreach ($q['categories'] as $catIndex => $catData) {
                    if (empty($catData['questions'])) {
                        continue;
                    }

                    $category = $quiz->categories()->create([
                        'label'      => $catData['label'] ?? ('Категория ' . ($catIndex + 1)),
                        'grade_from' => $catData['grade_from'] ?? null,
                        'grade_to'   => $catData['grade_to'] ?? null,
                        'sort_order' => $catData['sort_order'] ?? ($catIndex + 1),
                    ]);

                    foreach ($catData['questions'] as $qIndex => $qData) {
                        $imagePath   = null;
                        $imageSource = null;
                        $imageUrl    = null;

                        if (!empty($qData['image_data']) && !empty($qData['image_ext'])) {
                            $imagePath   = $this->saveBase64Image($qData['image_data'], $qData['image_ext']);
                            $imageSource = 'upload';
                        } elseif (!empty($qData['image_url'])) {
                            $imageUrl    = $qData['image_url'];
                            $imageSource = 'url';
                        }

                        $question = $quiz->questions()->create([
                            'quiz_category_id' => $category->id,
                            'question'         => $qData['question'],
                            'explanation'      => $qData['explanation'] ?? null,
                            'image_source'     => $imageSource,
                            'image_url'        => $imageSource === 'url' ? $imageUrl : null,
                            'image_path'       => $imageSource === 'upload' ? $imagePath : null,
                            'position'         => $qData['position'] ?? ($qIndex + 1),
                        ]);

                        $correctLabel = $qData['correct_answer'] ?? null;

                        foreach ($qData['answers'] as $aIndex => $aData) {
                            $label = $aData['label'] ?? chr(65 + $aIndex);
                            $question->answers()->create([
                                'label'      => $label,
                                'position'   => $aData['position'] ?? ($aIndex + 1),
                                'answer'     => $aData['answer'],
                                'is_correct' => $label === $correctLabel,
                            ]);
                        }
                    }
                }

                return $quiz;
            });
        } catch (\Throwable $e) {
            Log::error('QuizExportImportController: import failed', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ]);

            return response()->json([
                'message' => 'Не удалось импортировать олимпиаду.',
                'detail'  => app()->isLocal() ? $e->getMessage() : null,
            ], 422);
        }

        $quiz->load(['subject', 'categories.questions.answers'])->loadCount('questions');

        return response()->json([
            'message' => 'Олимпиада импортирована успешно.',
            'quiz_id' => $quiz->public_id,
            'title'   => $quiz->title,
            'questions_count' => $quiz->questions_count,
        ], 201);
    }

    private function resolveSubjectFromImport(array $subjectData): Subject
    {
        $name = trim($subjectData['name'] ?? '');

        if ($name) {
            $existing = Subject::where('name', $name)->first();
            if ($existing) {
                return $existing;
            }
        }

        $imagePath = null;
        if (!empty($subjectData['image_data']) && !empty($subjectData['image_ext'])) {
            $imagePath = $this->saveBase64Image($subjectData['image_data'], $subjectData['image_ext'], 'subject-images');
        }

        return Subject::create([
            'name'        => $name ?: 'Imported Subject',
            'description' => $subjectData['description'] ?? null,
            'image'       => $imagePath ?? ($subjectData['image_url'] ?? null),
            'start_date'  => $subjectData['start_date'] ?? now()->toDateString(),
            'end_date'    => $subjectData['end_date'] ?? null,
        ]);
    }

    private function saveBase64Image(string $data, string $ext, string $dir = 'question-images'): string
    {
        $ext      = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $ext)) ?: 'jpg';
        $filename = $dir . '/' . Str::uuid() . '.' . $ext;
        Storage::disk('public')->put($filename, base64_decode($data));

        return $filename;
    }
}
