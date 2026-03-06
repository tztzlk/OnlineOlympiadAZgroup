<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    /**
     * Список предметов
     */
    public function index()
    {
        try {
            return response()->json(
                Subject::orderBy('name')->get()
            );
        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Ошибка загрузки предметов'
            ], 500);
        }
    }

    /**
     * Получить один предмет
     */
    public function show($id)
    {
        try {
            $subject = Subject::find($id);

            if (!$subject) {
                return response()->json([
                    'message' => 'Предмет не найден'
                ], 404);
            }

            return response()->json($subject);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Ошибка сервера'
            ], 500);
        }
    }

    /**
     * Создание предмета (если нужно для админки)
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'start_date' => 'required|date'
        ]);

        try {
            $subject = Subject::create($request->all());

            return response()->json($subject, 201);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Ошибка создания предмета'
            ], 500);
        }
    }
}