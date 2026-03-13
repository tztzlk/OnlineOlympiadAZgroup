<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OlympiadRequest;
use App\Models\Quiz;
use App\Models\QuizResult;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'message' => 'Добро пожаловать в админ-панель',
            'users' => User::count(),
            'quizzes' => Quiz::count(),
            'requests' => [
                'total' => OlympiadRequest::count(),
                'pending' => OlympiadRequest::where('status', 'pending')->count(),
                'approved' => OlympiadRequest::where('status', 'approved')->count(),
                'rejected' => OlympiadRequest::where('status', 'rejected')->count(),
            ],
            'results' => QuizResult::count(),
        ]);
    }

    public function getUsers()
    {
        return response()->json(User::all());
    }

    public function usersResults()
    {
        $results = QuizResult::with(['user', 'quiz.subject'])
            ->latest()
            ->get()
            ->map(function (QuizResult $result) {
                $percent = $result->total > 0
                    ? (int) round(($result->score / $result->total) * 100)
                    : 0;

                return [
                    'id' => $result->id,
                    'user_name' => $result->user?->name ?? 'Unknown user',
                    'quiz_title' => $result->quiz?->title ?? 'Untitled quiz',
                    'subject' => $result->quiz?->subject?->name ?? 'Unknown subject',
                    'score' => $result->score,
                    'total' => $result->total,
                    'percent' => $percent,
                    'status' => $percent >= 60 ? 'passed' : 'failed',
                    'submitted_at' => optional($result->created_at)->toISOString(),
                    'date' => optional($result->created_at)->format('d.m.Y H:i'),
                ];
            });

        return response()->json($results);
    }
}
