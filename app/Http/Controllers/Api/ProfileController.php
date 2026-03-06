<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OlympiadRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\QuizResult;  
class ProfileController extends Controller
{

    // Получить профиль пользователя
    public function me(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Неавторизован'], 401);
        }

        return response()->json($user);
    }


    // ⭐ Последние олимпиады (для профиля)
    public function recentOlympiads(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Неавторизован'], 401);
        }

        return OlympiadRequest::with('subject')
            ->where('user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();
    }


    // ⭐ ВСЕ олимпиады (важно — ты используешь именно этот endpoint)
    public function olympiads(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Неавторизован'], 401);
        }

        return OlympiadRequest::with('subject')
            ->where('user_id', $user->id)
            ->get();
    }

    public function myResults(Request $request)
{
    $user = $request->user();

    if (!$user) {
        return response()->json(['message' => 'Не авторизован'], 401);
    }

    $results = QuizResult::with(['quiz.subject'])
        ->where('user_id', $user->id)
        ->latest()
        ->get();

    return response()->json(
        $results->map(function ($result) {

            $scorePercent = $result->total > 0
                ? round(($result->score / $result->total) * 100)
                : 0;

            return [
                'id' => $result->id,
                'subject' => $result->quiz?->subject?->name ?? 'Неизвестно',
                'date' => optional($result->created_at)->format('d.m.Y'),
                'score' => $scorePercent,
                'place' => $scorePercent >= 90 ? '1' : ($scorePercent >= 70 ? '3' : '-'),
                'status' => $scorePercent >= 60 ? 'Пройден' : 'Не пройден',
                'statusClass' => $scorePercent >= 60 ? 'win' : 'participant'
            ];
            
        })
        
    );
    
}
}
