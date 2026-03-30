<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuizResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ProfileController extends Controller
{
    public function me(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Не авторизован'], 401);
        }

        return response()->json($user);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Не авторизован'], 401);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:20|unique:users,phone,' . $user->id,
            'school' => 'required|string|max:255',
            'city' => 'required|string|max:255',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Профиль обновлён',
            'user' => $user->fresh(),
        ]);
    }

    public function recentOlympiads(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Не авторизован'], 401);
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
            return response()->json(['message' => 'Не авторизован'], 401);
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

        $results = QuizResult::with(['quiz.subject', 'category'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return response()->json(
            $results->map(function (QuizResult $result) use ($user) {
                $percent = $result->total > 0
                    ? (int) round(($result->score / $result->total) * 100)
                    : 0;

                return [
                    'id' => $result->id,
                    'subject' => $result->quiz?->subject?->name ?? 'Неизвестно',
                    'quiz_title' => $result->quiz?->title ?? 'Олимпиада',
                    'date' => optional($result->created_at)->format('d.m.Y H:i'),
                    'submitted_at' => optional($result->created_at)->toISOString(),
                    'school' => $user->school,
                    'city' => $user->city,
                    'category_label' => $result->category?->label ?? 'Общая',
                    'score' => $result->score,
                    'total' => $result->total,
                    'percent' => $percent,
                    'status' => $percent >= 60 ? 'Пройден' : 'Не пройден',
                    'statusClass' => $percent >= 60 ? 'win' : 'participant',
                    'certificate_url' => '/api/profile/results/' . $result->id . '/certificate',
                ];
            })
        );
    }

    public function certificate(Request $request, QuizResult $result)
    {
        $user = $request->user();

        if (!$user || $result->user_id !== $user->id) {
            abort(403);
        }

        $result->loadMissing(['quiz.subject', 'user', 'category']);

        $percent = $result->total > 0
            ? (int) round(($result->score / $result->total) * 100)
            : 0;

        $subject = $this->escapeSvg($result->quiz?->subject?->name ?? 'Олимпиада');
        $quizTitle = $this->escapeSvg($result->quiz?->title ?? 'Итоговый результат');
        $studentName = $this->escapeSvg($result->user?->name ?? 'Участник');
        $school = $this->escapeSvg($result->user?->school ?: 'Школа не указана');
        $city = $this->escapeSvg($result->user?->city ?: 'Город не указан');
        $category = $this->escapeSvg($result->category?->label ?? 'Общая');
        $date = $this->escapeSvg(optional($result->created_at)->format('d.m.Y'));
        $score = $this->escapeSvg("{$result->score} / {$result->total} ({$percent}%)");

        $svg = <<<SVG
<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<svg xmlns="http://www.w3.org/2000/svg" width="1600" height="1130" viewBox="0 0 1600 1130">
  <defs>
    <linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#fffaf2"/>
      <stop offset="100%" stop-color="#fef3c7"/>
    </linearGradient>
    <linearGradient id="accent" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#b91c1c"/>
      <stop offset="100%" stop-color="#f59e0b"/>
    </linearGradient>
  </defs>
  <rect width="1600" height="1130" rx="48" fill="url(#bg)"/>
  <rect x="28" y="28" width="1544" height="1074" rx="36" fill="none" stroke="#b45309" stroke-width="4"/>
  <rect x="68" y="68" width="1464" height="994" rx="28" fill="none" stroke="#f59e0b" stroke-width="2" stroke-dasharray="10 10"/>
  <text x="800" y="170" text-anchor="middle" font-size="38" font-family="Georgia, 'Times New Roman', serif" fill="#92400e">ONLINE OLYMPIAD</text>
  <text x="800" y="270" text-anchor="middle" font-size="76" font-family="Georgia, 'Times New Roman', serif" fill="#7c2d12">СЕРТИФИКАТ</text>
  <rect x="530" y="300" width="540" height="8" rx="4" fill="url(#accent)"/>
  <text x="800" y="390" text-anchor="middle" font-size="34" font-family="Arial, sans-serif" fill="#78350f">Подтверждает участие и получение результата</text>
  <text x="800" y="505" text-anchor="middle" font-size="64" font-family="Georgia, 'Times New Roman', serif" fill="#111827">{$studentName}</text>
  <text x="800" y="570" text-anchor="middle" font-size="30" font-family="Arial, sans-serif" fill="#374151">{$school}, {$city}</text>
  <text x="800" y="660" text-anchor="middle" font-size="28" font-family="Arial, sans-serif" fill="#92400e">Предмет: {$subject}</text>
  <text x="800" y="712" text-anchor="middle" font-size="28" font-family="Arial, sans-serif" fill="#92400e">Категория: {$category}</text>
  <text x="800" y="764" text-anchor="middle" font-size="28" font-family="Arial, sans-serif" fill="#92400e">Олимпиада: {$quizTitle}</text>
  <text x="800" y="816" text-anchor="middle" font-size="28" font-family="Arial, sans-serif" fill="#92400e">Результат: {$score}</text>
  <text x="800" y="868" text-anchor="middle" font-size="28" font-family="Arial, sans-serif" fill="#92400e">Дата: {$date}</text>
  <text x="170" y="980" font-size="24" font-family="Arial, sans-serif" fill="#6b7280">Сертификат сгенерирован автоматически системой online Olympiad</text>
  <text x="1230" y="980" font-size="24" font-family="Arial, sans-serif" fill="#6b7280">ID результата #{$result->id}</text>
</svg>
SVG;

        return Response::make($svg, 200, [
            'Content-Type' => 'image/svg+xml; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="certificate-result-' . $result->id . '.svg"',
        ]);
    }

    protected function escapeSvg(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
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
            'disqualified' => (bool) $item->disqualified_at,
            'disqualified_at' => optional($item->disqualified_at)->toISOString(),
            'disqualification_reason' => $item->disqualification_reason,
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
