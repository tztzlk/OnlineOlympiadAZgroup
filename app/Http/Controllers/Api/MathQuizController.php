<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MathQuestion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\QuizQuestion;

class MathQuizController extends Controller
{
    // Получение вопросов
  public function index()
{
    $user = auth()->user();

    if ($user->quiz_completed) {
        return response()->json([
            'questions' => [],
            'completed' => true
        ]);
    }

    $questions = QuizQuestion::where('subject', 'Математика')->get()->map(function($q) {
        return [
            'id' => $q->id,
            'question' => $q->question,
            'options' => $q->options,
        ];
    });

    return response()->json([
        'questions' => $questions,
        'completed' => false
    ]);
}


    // Проверка ответов и запись результата
    public function submit(Request $request)
{
    return DB::transaction(function () use ($request) {

        $user = \App\Models\User::where('id', Auth::id())
            ->lockForUpdate()
            ->first();

        if ($user->quiz_completed) {
            return response()->json([
                'message' => 'Вы уже проходили олимпиаду'
            ], 403);
        }

        $request->validate([
            'answers' => 'required|array'
        ]);

        $score = 0;
        $questions = MathQuestion::all();

        foreach ($questions as $q) {
            $userAnswer = $request->answers[$q->id] ?? null;

            if ((string)$userAnswer === (string)$q->answer) {
                $score++;
            }
        }

        $user->update([
            'quiz_score' => $score,
            'quiz_completed' => true,
        ]);

        return response()->json([
            'score' => $score,
            'total' => $questions->count(),
        ]);
    });


}                                             
}


    
