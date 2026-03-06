<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\QuizQuestion;

class AdminQuizController extends Controller
{
    // Получить все вопросы
    public function index()
    {
        return QuizQuestion::latest()->get();
    }

    // Добавить вопрос
    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string',
            'subject' => 'required|string',
            'options' => 'required|array|min:2',
            'answer' => 'required|string'
        ]);

        QuizQuestion::create([
            'question' => $request->question,
            'subject' => $request->subject,
            'options' => $request->options, // Laravel сам конвертирует в JSON
            'answer' => $request->answer
        ]);

        return response()->json([
            'message' => 'Вопрос добавлен'
        ]);
    }

    // Обновить
    public function update(Request $request, $id)
    {
        $question = QuizQuestion::findOrFail($id);

        $question->update([
            'question' => $request->question,
            'subject' => $request->subject,
            'options' => $request->options,
            'answer' => $request->answer
        ]);

        return response()->json([
            'message' => 'Вопрос обновлён'
        ]);
    }

    // Удалить
    public function destroy($id)
    {
        QuizQuestion::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Удалено'
        ]);
    }
}
