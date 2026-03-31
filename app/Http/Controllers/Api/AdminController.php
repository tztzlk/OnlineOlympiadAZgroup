<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CallbackRequest;
use App\Models\ChildProfile;
use App\Models\OlympiadRequest;
use App\Models\PaymentRecord;
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
            'children' => ChildProfile::count(),
            'quizzes' => Quiz::count(),
            'requests' => [
                'total' => OlympiadRequest::count(),
                'pending' => OlympiadRequest::where('status', 'pending')->count(),
                'approved' => OlympiadRequest::where('status', 'approved')->count(),
                'rejected' => OlympiadRequest::where('status', 'rejected')->count(),
            ],
            'results' => QuizResult::count(),
            'payments' => PaymentRecord::count(),
            'callbacks' => CallbackRequest::count(),
        ]);
    }

    public function getUsers()
    {
        return response()->json(User::with('childProfiles')->get());
    }

    public function usersResults(Request $request)
    {
        return response()->json($this->buildResults($request));
    }

    public function exportUsersResults(Request $request): StreamedResponse
    {
        return $this->downloadWorksheet('Results', 'results', [
            ['Ребенок', 'Родитель', 'Школа', 'Город', 'Предмет', 'Категория', 'Олимпиада', 'Баллы', 'Процент', 'Статус', 'Дата'],
            ...$this->buildResults($request)->map(fn ($result) => [
                $result['child_name'],
                $result['parent_name'],
                $result['school'],
                $result['city'],
                $result['subject'],
                $result['category'],
                $result['quiz_title'],
                $result['score'] . '/' . $result['total'],
                $result['percent'] . '%',
                $result['status'] === 'passed' ? 'Пройден' : 'Не пройден',
                $result['date'],
            ]),
        ]);
    }

    public function participants()
    {
        $rows = ChildProfile::with('parent')
            ->latest()
            ->get()
            ->map(fn (ChildProfile $child) => [
                'id' => $child->id,
                'child_name' => $child->full_name,
                'grade' => $child->grade,
                'school' => $child->school,
                'city' => $child->city,
                'language_preference' => $child->language_preference,
                'parent_name' => $child->parent?->name,
                'parent_email' => $child->parent?->email,
                'parent_phone' => $child->parent?->phone,
                'created_at' => optional($child->created_at)->format('d.m.Y H:i'),
            ]);

        return response()->json($rows);
    }

    public function exportParticipants(): StreamedResponse
    {
        $rows = $this->participants()->getData(true);

        return $this->downloadWorksheet('Participants', 'participants', [
            ['ID', 'Ребенок', 'Класс', 'Школа', 'Город', 'Язык', 'Родитель', 'Email', 'Телефон', 'Создан'],
            ...collect($rows)->map(fn ($row) => [
                (string) $row['id'],
                $row['child_name'],
                (string) $row['grade'],
                $row['school'],
                $row['city'],
                $row['language_preference'],
                $row['parent_name'],
                $row['parent_email'],
                $row['parent_phone'],
                $row['created_at'],
            ]),
        ]);
    }

    public function importParticipants(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $contents = file_get_contents($request->file('file')->getRealPath());
        $rows = preg_split('/\r\n|\n|\r/', trim((string) $contents));
        $header = null;
        $imported = 0;
        $errors = [];

        foreach ($rows as $lineNumber => $line) {
            if ($line === '') {
                continue;
            }

            $columns = str_getcsv($line, ';');
            if ($header === null) {
                $header = array_map(fn ($value) => trim((string) $value), $columns);
                continue;
            }

            $row = array_combine($header, $columns);
            $email = trim((string) ($row['parent_email'] ?? ''));
            $childFirst = trim((string) ($row['child_first_name'] ?? ''));
            $childLast = trim((string) ($row['child_last_name'] ?? ''));

            if ($email === '' || $childFirst === '' || $childLast === '') {
                $errors[] = [
                    'line' => $lineNumber + 1,
                    'message' => 'Не заполнены обязательные поля parent_email / child_first_name / child_last_name',
                ];
                continue;
            }

            $parent = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => trim((string) ($row['parent_name'] ?? $email)),
                    'phone' => trim((string) ($row['parent_phone'] ?? ('import-' . uniqid()))),
                    'school' => trim((string) ($row['school'] ?? '')),
                    'city' => trim((string) ($row['city'] ?? '')),
                    'password' => bcrypt('TempPass123!'),
                ]
            );

            ChildProfile::updateOrCreate(
                [
                    'parent_id' => $parent->id,
                    'first_name' => $childFirst,
                    'last_name' => $childLast,
                ],
                [
                    'grade' => is_numeric($row['grade'] ?? null) ? (int) $row['grade'] : null,
                    'birth_date' => !empty($row['birth_date']) ? $row['birth_date'] : null,
                    'school' => trim((string) ($row['school'] ?? $parent->school)),
                    'city' => trim((string) ($row['city'] ?? $parent->city)),
                    'language_preference' => trim((string) ($row['language_preference'] ?? 'ru')),
                ]
            );

            $imported++;
        }

        return response()->json([
            'message' => 'Импорт завершен',
            'imported' => $imported,
            'errors' => $errors,
        ]);
    }

    public function payments()
    {
        $rows = PaymentRecord::with(['parent', 'childProfile', 'subject'])
            ->latest()
            ->get()
            ->map(fn (PaymentRecord $payment) => [
                'id' => $payment->id,
                'parent_name' => $payment->parent?->name,
                'child_name' => $payment->childProfile?->full_name,
                'subject' => $payment->subject?->name,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'status' => $payment->status,
                'external_reference' => $payment->external_reference,
                'comment' => $payment->comment,
                'date' => optional($payment->created_at)->format('d.m.Y H:i'),
                'paid_at' => optional($payment->paid_at)->format('d.m.Y H:i'),
            ]);

        return response()->json($rows);
    }

    public function exportPayments(): StreamedResponse
    {
        $rows = $this->payments()->getData(true);

        return $this->downloadWorksheet('Payments', 'payments', [
            ['ID', 'Родитель', 'Ребенок', 'Предмет', 'Сумма', 'Валюта', 'Статус', 'Внешний номер', 'Комментарий', 'Создан', 'Оплачен'],
            ...collect($rows)->map(fn ($row) => [
                (string) $row['id'],
                $row['parent_name'],
                $row['child_name'],
                $row['subject'],
                (string) $row['amount'],
                $row['currency'],
                $row['status'],
                $row['external_reference'],
                $row['comment'],
                $row['date'],
                $row['paid_at'],
            ]),
        ]);
    }

    public function callbacks()
    {
        return response()->json(
            CallbackRequest::latest()
                ->get()
                ->map(fn (CallbackRequest $callback) => [
                    'id' => $callback->id,
                    'name' => $callback->name,
                    'phone' => $callback->phone,
                    'email' => $callback->email,
                    'message' => $callback->message,
                    'status' => $callback->status,
                    'date' => optional($callback->created_at)->format('d.m.Y H:i'),
                ])
        );
    }

    public function exportCallbacks(): StreamedResponse
    {
        $rows = $this->callbacks()->getData(true);

        return $this->downloadWorksheet('Callbacks', 'callbacks', [
            ['ID', 'Имя', 'Телефон', 'Email', 'Сообщение', 'Статус', 'Дата'],
            ...collect($rows)->map(fn ($row) => [
                (string) $row['id'],
                $row['name'],
                $row['phone'],
                $row['email'],
                $row['message'],
                $row['status'],
                $row['date'],
            ]),
        ]);
    }

    protected function buildResults(Request $request): Collection
    {
        $search = mb_strtolower(trim((string) $request->string('search')));
        $status = (string) $request->string('status', 'all');
        $subject = trim((string) $request->string('subject', 'all'));

        return QuizResult::with(['user', 'childProfile', 'quiz.subject', 'category'])
            ->latest()
            ->get()
            ->map(function (QuizResult $result) {
                $percent = $result->total > 0
                    ? (int) round(($result->score / $result->total) * 100)
                    : 0;

                return [
                    'id' => $result->id,
                    'child_name' => $result->childProfile?->full_name ?? ($result->user?->name ?? 'Unknown child'),
                    'parent_name' => $result->user?->name ?? 'Unknown parent',
                    'school' => $result->childProfile?->school ?? ($result->user?->school ?? 'Не указана'),
                    'city' => $result->childProfile?->city ?? ($result->user?->city ?? 'Не указан'),
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
                    $row['child_name'],
                    $row['parent_name'],
                    $row['school'],
                    $row['city'],
                    $row['quiz_title'],
                    $row['subject'],
                    $row['category'],
                ]));

                return $matchesStatus && $matchesSubject && ($search === '' || str_contains($haystack, $search));
            })
            ->values();
    }

    protected function downloadWorksheet(string $sheetName, string $prefix, array $rows): StreamedResponse
    {
        $xmlRows = collect($rows)->map(function (array $row) {
            $cells = collect($row)->map(function ($cell) {
                return '<Cell><Data ss:Type="String">' . htmlspecialchars((string) $cell, ENT_QUOTES | ENT_XML1, 'UTF-8') . '</Data></Cell>';
            })->implode('');

            return '<Row>' . $cells . '</Row>';
        })->implode('');

        $xml = <<<XML
<?xml version="1.0"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">
  <Worksheet ss:Name="{$sheetName}">
    <Table>
      {$xmlRows}
    </Table>
  </Worksheet>
</Workbook>
XML;

        return response()->streamDownload(function () use ($xml) {
            echo $xml;
        }, $prefix . '-' . now()->format('Y-m-d-His') . '.xls', [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }
}
