<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OlympiadRequest;
use App\Http\Resources\OlympiadRequestResource;

class OlympiadRequestController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Создание заявки
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'grade' => 'required|string|max:50',
            'language' => 'required|string|max:10',
            'parent_name' => 'required|string|max:255',
            'parent_phone' => 'required|string|max:20',
            'parent_email' => 'required|email|max:255',
        ]);

        $data['user_id'] = $user->id;

        // Duplicate protection
        $exists = OlympiadRequest::where('user_id', $user->id)
            ->where('subject_id', $data['subject_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Вы уже подавали заявку'
            ], 400);
        }

        $requestModel = OlympiadRequest::create($data);

        $requestModel->load('subject');

        return response()->json([
            'message' => 'Заявка создана',
            'request' => new OlympiadRequestResource($requestModel),
            'payment_qr' => url('/storage/qr/default-qr.png')
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Статус заявки пользователя
    |--------------------------------------------------------------------------
    */
    public function status(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['status' => null]);
        }

        $requestModel = OlympiadRequest::where('user_id', $user->id)
            ->latest()
            ->first();

        return response()->json([
            'status' => $requestModel?->status
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Админ — изменение статуса
    |--------------------------------------------------------------------------
    */
    public function approveReject(Request $request, OlympiadRequest $olympiadRequest)
    {
        $user = $request->user();

        // ❗ Без middleware — проверяем вручную
        if (!$user || !$user->is_admin) {
            return response()->json([
                'message' => 'Forbidden'
            ], 403);
        }

        $request->validate([
            'status' => 'required|in:approved,rejected'
        ]);

        $olympiadRequest->status = $request->status;
        $olympiadRequest->save();

        $olympiadRequest->load('subject');

        return response()->json([
            'message' => "Заявка {$request->status}",
            'request' => new OlympiadRequestResource($olympiadRequest)
        ]);
    }
}