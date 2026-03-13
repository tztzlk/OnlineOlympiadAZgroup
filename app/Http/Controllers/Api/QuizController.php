<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OlympiadRequest;
use App\Models\Quiz;
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
            ->pluck('subject_id');

        $quizzes = Quiz::with('subject')
            ->withCount('questions')
            ->where('is_published', true)
            ->whereIn('subject_id', $approvedSubjects)
            ->get();

        return response()->json($quizzes);
    }

    public function getQuiz($subjectId)
    {
        $user = Auth::user();

        $approved = OlympiadRequest::where('user_id', $user->id)
            ->where('subject_id', $subjectId)
            ->where('status', 'approved')
            ->exists();

        if (!$approved) {
            return response()->json([
                'message' => 'Вы не допущены к олимпиаде',
            ], 403);
        }

        $quiz = Quiz::where('subject_id', $subjectId)
            ->where('is_published', true)
            ->with([
                'subject',
                'questions',
                'questions.answers:id,question_id,label,position,answer',
            ])
            ->first();

        if (!$quiz) {
            return response()->json([
                'message' => 'Тест не найден',
            ], 404);
        }

        $alreadySubmitted = QuizResult::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->exists();

        return response()->json([
            'id' => $quiz->id,
            'subject_id' => $quiz->subject_id,
            'title' => $quiz->title,
            'description' => $quiz->description,
            'time_limit' => $quiz->time_limit,
            'questions_count' => $quiz->questions->count(),
            'already_submitted' => $alreadySubmitted,
            'subject' => $quiz->subject,
            'questions' => $quiz->questions->values()->map(function ($question) {
                return [
                    'id' => $question->id,
                    'question' => $question->question,
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

        $status = OlympiadRequest::where('user_id', $user->id)
            ->where('subject_id', $subjectId)
            ->latest()
            ->value('status');

        return response()->json([
            'status' => $status,
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
            return response()->json([
                'message' => 'Тест уже был пройден',
            ], 403);
        }

        $quiz = Quiz::with('questions.answers')->findOrFail($quizId);

        if (!$quiz->is_published) {
            return response()->json([
                'message' => 'Тест пока не опубликован',
            ], 403);
        }

        $approved = OlympiadRequest::where('user_id', $user->id)
            ->where('subject_id', $quiz->subject_id)
            ->where('status', 'approved')
            ->exists();

        if (!$approved) {
            return response()->json([
                'message' => 'Вы не допущены к олимпиаде',
            ], 403);
        }

        $answers = $request->input('answers', []);
        $score = 0;
        $total = $quiz->questions->count();

        foreach ($quiz->questions as $question) {
            if (!isset($answers[$question->id])) {
                continue;
            }

            $correct = $question->answers->firstWhere('is_correct', true);
            if ($correct && (int) $correct->id === (int) $answers[$question->id]) {
                $score++;
            }
        }

        QuizResult::create([
            'user_id' => $user->id,
            'quiz_id' => $quizId,
            'score' => $score,
            'total' => $total,
        ]);

        OlympiadRequest::where('user_id', $user->id)
            ->where('subject_id', $quiz->subject_id)
            ->update(['completed' => true]);

        $percent = $total > 0 ? (int) round(($score / $total) * 100) : 0;

        return response()->json([
            'message' => 'Тест завершен',
            'score' => $score,
            'total' => $total,
            'percent' => $percent,
            'status' => $percent >= 60 ? 'passed' : 'failed',
        ]);
    }
}
