<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OlympiadRequest;
use App\Models\Quiz;
use App\Models\QuizResult;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function usersResults(Request $request)
    {
        return response()->json($this->buildResults($request));
    }

    public function exportUsersResults(Request $request): StreamedResponse
    {
        $xml = $this->buildExcelXml($this->buildResults($request));

        return response()->streamDownload(function () use ($xml) {
            echo $xml;
        }, 'results-' . now()->format('Y-m-d-His') . '.xls', [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    protected function buildResults(Request $request): Collection
    {
        $search = mb_strtolower(trim((string) $request->string('search')));
        $status = (string) $request->string('status', 'all');
        $subject = trim((string) $request->string('subject', 'all'));

        return QuizResult::with(['user', 'quiz.subject', 'category'])
            ->latest()
            ->get()
            ->map(function (QuizResult $result) {
                $percent = $result->total > 0
                    ? (int) round(($result->score / $result->total) * 100)
                    : 0;

                return [
                    'id' => $result->id,
                    'user_name' => $result->user?->name ?? 'Unknown user',
                    'school' => $result->user?->school ?? 'Не указана',
                    'city' => $result->user?->city ?? 'Не указан',
                    'quiz_title' => $result->quiz?->title ?? 'Untitled quiz',
                    'subject' => $result->quiz?->subject?->name ?? 'Unknown subject',
                    'category' => $result->category?->label ?? 'Общая',
                    'score' => $result->score,
                    'total' => $result->total,
                    'percent' => $percent,
                    'status' => $percent >= 60 ? 'passed' : 'failed',
                    'submitted_at' => optional($result->created_at)->toISOString(),
                    'date' => optional($result->created_at)->format('d.m.Y H:i'),
                ];
            })
            ->filter(function (array $row) use ($search, $status, $subject) {
                $matchesStatus = $status === 'all' || $row['status'] === $status;
                $matchesSubject = $subject === '' || $subject === 'all' || $row['subject'] === $subject;
                $haystack = mb_strtolower(implode(' ', [
                    $row['user_name'],
                    $row['school'],
                    $row['city'],
                    $row['quiz_title'],
                    $row['subject'],
                    $row['category'],
                ]));
                $matchesSearch = $search === '' || str_contains($haystack, $search);

                return $matchesStatus && $matchesSubject && $matchesSearch;
            })
            ->values();
    }

    protected function buildExcelXml(Collection $results): string
    {
        $rows = [
            ['ФИО', 'Школа', 'Город', 'Предмет', 'Категория', 'Олимпиада', 'Баллы', 'Процент', 'Статус', 'Дата'],
        ];

        foreach ($results as $result) {
            $rows[] = [
                $result['user_name'],
                $result['school'],
                $result['city'],
                $result['subject'],
                $result['category'],
                $result['quiz_title'],
                $result['score'] . '/' . $result['total'],
                $result['percent'] . '%',
                $result['status'] === 'passed' ? 'Пройден' : 'Не пройден',
                $result['date'],
            ];
        }

        $xmlRows = collect($rows)->map(function (array $row) {
            $cells = collect($row)->map(function (string $cell) {
                return '<Cell><Data ss:Type="String">' . htmlspecialchars($cell, ENT_QUOTES | ENT_XML1, 'UTF-8') . '</Data></Cell>';
            })->implode('');

            return '<Row>' . $cells . '</Row>';
        })->implode('');

        return <<<XML
<?xml version="1.0"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">
  <Worksheet ss:Name="Results">
    <Table>
      {$xmlRows}
    </Table>
  </Worksheet>
</Workbook>
XML;
    }
}
