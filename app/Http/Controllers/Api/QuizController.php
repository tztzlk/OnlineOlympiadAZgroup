<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\QuizResult;
use App\Models\OlympiadRequest;
use Illuminate\Support\Facades\Auth;

class QuizController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Получить доступные тесты
    |--------------------------------------------------------------------------
    */

    public function getSubjects()
    {
        $user = Auth::user();

        $approvedSubjects = OlympiadRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->pluck('subject_id');

        $quizzes = Quiz::with('subject')
            ->whereIn('subject_id', $approvedSubjects)
            ->get();

        return response()->json($quizzes);
    }


    /*
    |--------------------------------------------------------------------------
    | Получить тест
    |--------------------------------------------------------------------------
    */

    public function getQuiz($subjectId)
    {
        $user = Auth::user();

        $approved = OlympiadRequest::where('user_id', $user->id)
            ->where('subject_id', $subjectId)
            ->where('status', 'approved')
            ->exists();

        if (!$approved) {
            return response()->json([
                'message' => 'Вы не допущены к олимпиаде'
            ], 403);
        }

        $quiz = Quiz::where('subject_id', $subjectId)
            ->with([
                'questions.answers:id,question_id,answer,is_correct'
            ])
            ->first();

        if (!$quiz) {
            return response()->json([
                'message' => 'Тест не найден'
            ], 404);
        }

        return response()->json($quiz);
    }


    /*
    |--------------------------------------------------------------------------
    | Статус допуска к олимпиаде
    |--------------------------------------------------------------------------
    */

    public function getStatus($subjectId)
    {
        $user = Auth::user();

        $status = OlympiadRequest::where('user_id', $user->id)
            ->where('subject_id', $subjectId)
            ->latest()
            ->value('status');

        return response()->json([
            'status' => $status
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Отправка результатов теста
    |--------------------------------------------------------------------------
    */

    public function submitQuiz(Request $request, $quizId)
    {
        $user = Auth::user();

        $request->validate([
            'answers' => 'required|array'
        ]);

        $exists = QuizResult::where('user_id', $user->id)
            ->where('quiz_id', $quizId)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Тест уже был пройден'
            ], 403);
        }

        $quiz = Quiz::with('questions.answers')->findOrFail($quizId);

        $answers = $request->answers;

        $score = 0;
        $total = $quiz->questions->count();

        foreach ($quiz->questions as $question) {

            if (!isset($answers[$question->id])) {
                continue;
            }

            $correct = $question->answers
                ->where('is_correct', 1)
                ->first();

            if ($correct && $correct->id == $answers[$question->id]) {
                $score++;
            }
        }

        QuizResult::create([
            'user_id' => $user->id,
            'quiz_id' => $quizId,
            'score' => $score,
            'total' => $total
        ]);

        return response()->json([
            'message' => 'Тест завершен',
            'score' => $score,
            'total' => $total
        ]);
    }
}