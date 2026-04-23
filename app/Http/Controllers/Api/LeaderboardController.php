<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    public function index()
    {
        $leaderboard = Cache::remember('leaderboard:top10', 300, fn () => $this->buildLeaderboard());

        return response()->json($leaderboard);
    }

    private function buildLeaderboard(): \Illuminate\Support\Collection
    {
        $percentExpression = 'CASE WHEN qr.total > 0 THEN ROUND((qr.score * 100.0) / qr.total) ELSE 0 END';

        $rankedResults = DB::table('quiz_results as qr')
            ->selectRaw("
                qr.id,
                qr.user_id,
                qr.quiz_id,
                qr.quiz_category_id,
                qr.score,
                qr.total,
                qr.created_at,
                {$percentExpression} as percent,
                ROW_NUMBER() OVER (
                    PARTITION BY qr.user_id
                    ORDER BY {$percentExpression} DESC, qr.score DESC, qr.created_at DESC, qr.id DESC
                ) as user_rank
            ");

        $leaderboard = DB::query()
            ->fromSub($rankedResults, 'ranked')
            ->leftJoin('users', 'users.id', '=', 'ranked.user_id')
            ->leftJoin('quizzes', 'quizzes.id', '=', 'ranked.quiz_id')
            ->leftJoin('subjects', 'subjects.id', '=', 'quizzes.subject_id')
            ->leftJoin('quiz_categories', 'quiz_categories.id', '=', 'ranked.quiz_category_id')
            ->where('ranked.user_rank', 1)
            ->orderByDesc('ranked.percent')
            ->orderByDesc('ranked.score')
            ->orderByDesc('ranked.created_at')
            ->limit(10)
            ->get([
                'ranked.score',
                'ranked.total',
                'ranked.percent',
                'ranked.created_at',
                DB::raw("COALESCE(users.name, 'Участник') as user_name"),
                DB::raw("COALESCE(users.school, 'Не указана') as school"),
                DB::raw("COALESCE(users.city, 'Не указан') as city"),
                DB::raw("COALESCE(subjects.name, 'Неизвестный предмет') as subject"),
                DB::raw("COALESCE(quiz_categories.label, 'Общая') as category"),
            ])
            ->values()
            ->map(function ($item, int $index) {
                return [
                    'rank' => $index + 1,
                    'user_name' => $this->maskParticipantName($item->user_name),
                    'school' => $item->school,
                    'city' => $item->city,
                    'subject' => $item->subject,
                    'category' => $item->category,
                    'score' => $item->score,
                    'total' => $item->total,
                    'percent' => (int) $item->percent,
                    'date' => optional($item->created_at)->format('d.m.Y'),
                ];
            });

        return $leaderboard;
    }

    protected function maskParticipantName(?string $value): string
    {
        $parts = preg_split('/\s+/u', trim((string) $value), -1, PREG_SPLIT_NO_EMPTY);

        if (!$parts) {
            return 'Участник';
        }

        return collect($parts)
            ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)) . str_repeat('*', max(mb_strlen($part) - 1, 1)))
            ->implode(' ');
    }
}
