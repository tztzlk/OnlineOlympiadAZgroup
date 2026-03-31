<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
                ->first();

            if (!$subject) {
                return response()->json([
                    'message' => 'Предмет не найден.',
                ], 404);
            }

            return response()->json($this->mapSubject($subject));
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
        ];
    }
}
