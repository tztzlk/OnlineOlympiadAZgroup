<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\Subject;
use Illuminate\Http\Request;

class AdminQuizController extends Controller
{
    public function index()
    {
        return response()->json(
            Quiz::with('subject')
                ->withCount('questions')
                ->latest()
                ->get()
        );
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);
        $subject = $this->resolveSubject($data);

        $quiz = Quiz::create([
            'subject_id' => $subject->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'time_limit' => $data['time_limit'],
            'is_published' => (bool) ($data['is_published'] ?? false),
        ]);

        $this->syncQuestions($quiz, $data['questions']);

        return response()->json(
            $quiz->load('subject')->loadCount('questions'),
            201
        );
    }

    public function show(Quiz $quiz)
    {
        $quiz->load(['subject', 'questions.answers'])->loadCount('questions');

        return response()->json($quiz);
    }

    public function update(Request $request, Quiz $quiz)
    {
        $data = $this->validatePayload($request);
        $subject = $this->resolveSubject($data);

        $quiz->update([
            'subject_id' => $subject->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'time_limit' => $data['time_limit'],
            'is_published' => (bool) ($data['is_published'] ?? false),
        ]);

        $this->syncQuestions($quiz, $data['questions']);

        return response()->json(
            $quiz->fresh()->load('subject')->loadCount('questions')
        );
    }

    public function destroy(Quiz $quiz)
    {
        $quiz->delete();

        return response()->json([
            'message' => 'Quiz deleted',
        ]);
    }

    public function publish(Quiz $quiz)
    {
        $quiz->update(['is_published' => true]);

        return response()->json([
            'message' => 'Quiz published',
            'quiz' => $quiz->fresh()->load('subject')->loadCount('questions'),
        ]);
    }

    public function unpublish(Quiz $quiz)
    {
        $quiz->update(['is_published' => false]);

        return response()->json([
            'message' => 'Quiz unpublished',
            'quiz' => $quiz->fresh()->load('subject')->loadCount('questions'),
        ]);
    }

    protected function validatePayload(Request $request): array
    {
        return $request->validate([
            'subject_id' => 'nullable|exists:subjects,id',
            'subject.name' => 'required_without:subject_id|string|max:255',
            'subject.description' => 'nullable|string',
            'subject.image' => 'nullable|string',
            'subject.start_date' => 'nullable|date',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_limit' => 'required|integer|min:1|max:180',
            'is_published' => 'sometimes|boolean',
            'questions' => 'required|array|min:1|max:100',
            'questions.*.question' => 'required|string',
            'questions.*.position' => 'nullable|integer|min:1|max:100',
            'questions.*.answers' => 'required|array|size:5',
            'questions.*.correct_answer' => 'required|string|in:A,B,C,D,E',
            'questions.*.answers.*.label' => 'required|string|in:A,B,C,D,E',
            'questions.*.answers.*.answer' => 'required|string',
            'questions.*.answers.*.position' => 'nullable|integer|min:1|max:5',
        ]);
    }

    protected function resolveSubject(array $data): Subject
    {
        if (!empty($data['subject_id'])) {
            return Subject::findOrFail($data['subject_id']);
        }

        return Subject::create([
            'name' => $data['subject']['name'],
            'description' => $data['subject']['description'] ?? null,
            'image' => $data['subject']['image'] ?? null,
            'start_date' => $data['subject']['start_date'] ?? now()->toDateString(),
        ]);
    }

    protected function syncQuestions(Quiz $quiz, array $questions): void
    {
        $quiz->questions()->delete();

        foreach ($questions as $questionIndex => $questionData) {
            $question = $quiz->questions()->create([
                'question' => $questionData['question'],
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
