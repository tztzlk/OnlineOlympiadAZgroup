<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuizResult;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function me(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Неавторизован'], 401);
        }

        return response()->json($user);
    }

    public function recentOlympiads(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Неавторизован'], 401);
        }

        return $user->olympiadRequests()
            ->with('subject')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($item) => $this->mapOlympiadRequest($item));
    }

    public function olympiads(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Неавторизован'], 401);
        }

        return $user->olympiadRequests()
            ->with('subject')
            ->latest()
            ->get()
            ->map(fn ($item) => $this->mapOlympiadRequest($item));
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
                $percent = $result->total > 0
                    ? (int) round(($result->score / $result->total) * 100)
                    : 0;

                return [
                    'id' => $result->id,
                    'subject' => $result->quiz?->subject?->name ?? 'Неизвестно',
                    'quiz_title' => $result->quiz?->title ?? 'Олимпиада',
                    'date' => optional($result->created_at)->format('d.m.Y H:i'),
                    'submitted_at' => optional($result->created_at)->toISOString(),
                    'score' => $result->score,
                    'total' => $result->total,
                    'percent' => $percent,
                    'status' => $percent >= 60 ? 'Пройден' : 'Не пройден',
                    'statusClass' => $percent >= 60 ? 'win' : 'participant',
                ];
            })
        );
    }

    protected function mapOlympiadRequest($item): array
    {
        $quizId = $item->subject?->quizzes()->value('id');
        $completed = false;

        if ($quizId) {
            $completed = QuizResult::where('user_id', $item->user_id)
                ->where('quiz_id', $quizId)
                ->exists();
        }

        return [
            'id' => $item->id,
            'status' => $item->status,
            'completed' => $completed,
            'subject' => [
                'id' => $item->subject?->id,
                'name' => $item->subject?->name,
                'image' => $item->subject?->image,
                'description' => $item->subject?->description,
                'start_date' => optional($item->subject?->start_date)->toDateString(),
            ],
        ];
    }
}
