<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CallbackRequest;
use App\Models\ChildProfile;
use App\Models\OlympiadRequest;
use App\Models\PaymentImportRow;
use App\Models\PaymentRecord;
use App\Models\PlatformNotification;
use App\Models\Quiz;
use App\Models\QuizResult;
use App\Models\User;
use App\Support\KaspiCsvImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalRequests = OlympiadRequest::count();
        $approvedRequests = OlympiadRequest::where('status', 'approved')->count();
        $rejectedRequests = OlympiadRequest::where('status', 'rejected')->count();
        $paidRequests = OlympiadRequest::where('payment_status', 'paid')->count();
        $completedRequests = OlympiadRequest::where('completed', true)->count();
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();

        return response()->json([
            'message' => 'Добро пожаловать в админ-панель',
            'users' => User::count(),
            'children' => ChildProfile::count(),
            'quizzes' => Quiz::count(),
            'requests' => [
                'total' => $totalRequests,
                'pending' => OlympiadRequest::where('status', 'pending')->count(),
                'approved' => $approvedRequests,
                'rejected' => $rejectedRequests,
            ],
            'results' => QuizResult::count(),
            'payments' => PaymentRecord::count(),
            'callbacks' => CallbackRequest::count(),
            'queues' => [
                'pending_requests' => OlympiadRequest::where('status', 'pending')
                    ->latest()
                    ->take(5)
                    ->with(['user', 'subject', 'childProfile'])
                    ->get()
                    ->map(fn (OlympiadRequest $item) => [
                        'id' => $item->public_id,
                        'parent_name' => $item->user?->name,
                        'child_name' => $item->childProfile?->full_name,
                        'subject' => $item->subject?->name,
                        'created_at' => optional($item->created_at)->format('d.m.Y H:i'),
                    ]),
                'payment_review' => PaymentRecord::whereIn('status', ['pending', 'failed'])
                    ->latest()
                    ->take(5)
                    ->with(['parent', 'childProfile', 'subject'])
                    ->get()
                    ->map(fn (PaymentRecord $payment) => [
                        'id' => $payment->public_id,
                        'parent_name' => $payment->parent?->name,
                        'child_name' => $payment->childProfile?->full_name,
                        'subject' => $payment->subject?->name,
                        'status' => $payment->status,
                        'reconciliation_status' => $payment->reconciliation_status,
                        'created_at' => optional($payment->created_at)->format('d.m.Y H:i'),
                    ]),
                'callbacks' => CallbackRequest::where('status', 'pending')
                    ->latest()
                    ->take(5)
                    ->get()
                    ->map(fn (CallbackRequest $callback) => [
                        'id' => $callback->public_id,
                        'type' => $callback->type ?? 'callback',
                        'name' => $callback->name,
                        'topic' => $callback->topic,
                        'phone' => $callback->phone,
                        'created_at' => optional($callback->created_at)->format('d.m.Y H:i'),
                    ]),
            ],
            'funnel' => [
                'created' => $totalRequests,
                'approved' => $approvedRequests,
                'rejected' => $rejectedRequests,
                'paid' => $paidRequests,
                'completed' => $completedRequests,
            ],
            'payment_distribution' => [
                'pending' => PaymentRecord::where('status', 'pending')->count(),
                'paid' => PaymentRecord::where('status', 'paid')->count(),
                'failed' => PaymentRecord::where('status', 'failed')->count(),
            ],
            'top_subjects' => QuizResult::query()
                ->selectRaw('subjects.id, subjects.name as subject_name, COUNT(quiz_results.id) as results_count, ROUND(AVG((quiz_results.score / NULLIF(quiz_results.total, 0)) * 100), 0) as average_percent')
                ->join('quizzes', 'quizzes.id', '=', 'quiz_results.quiz_id')
                ->join('subjects', 'subjects.id', '=', 'quizzes.subject_id')
                ->groupBy('subjects.id', 'subjects.name')
                ->orderByDesc('results_count')
                ->limit(5)
                ->get()
                ->map(fn ($row) => [
                    'subject' => $row->subject_name,
                    'results_count' => (int) $row->results_count,
                    'average_percent' => (int) ($row->average_percent ?? 0),
                ]),
            'weekly' => [
                'users' => User::whereBetween('created_at', [$weekStart, $weekEnd])->count(),
                'requests' => OlympiadRequest::whereBetween('created_at', [$weekStart, $weekEnd])->count(),
                'payments' => PaymentRecord::whereBetween('created_at', [$weekStart, $weekEnd])->count(),
                'callbacks' => CallbackRequest::whereBetween('created_at', [$weekStart, $weekEnd])->count(),
                'results' => QuizResult::whereBetween('created_at', [$weekStart, $weekEnd])->count(),
            ],
            'notifications' => [
                'unread' => PlatformNotification::where('for_admin', true)->whereNull('read_at')->count(),
            ],
        ]);
    }

    public function getUsers()
    {
        return response()->json(User::with('childProfiles')->paginate(100));
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
        $createdParents = 0;
        $createdParentEmails = [];
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

            $row = array_combine($header, array_slice($columns, 0, count($header)));

            if ($row === false) {
                $errors[] = [
                    'line' => $lineNumber + 1,
                    'message' => 'Некорректный формат CSV: количество колонок не совпадает с заголовками',
                ];
                continue;
            }

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

            $parent = User::query()->where('email', $email)->first();

            if (!$parent) {
                $parent = User::create([
                    'name' => trim((string) ($row['parent_name'] ?? $email)),
                    'email' => $email,
                    'phone' => trim((string) ($row['parent_phone'] ?? ('import-' . uniqid()))),
                    'school' => trim((string) ($row['school'] ?? '')),
                    'city' => trim((string) ($row['city'] ?? '')),
                    'password' => Hash::make((string) Str::password(32)),
                ]);

                $createdParents++;
                $createdParentEmails[] = $email;
            }

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

        Log::channel('security')->info('admin.import_participants', [
            'admin_id' => $request->user()?->id,
            'imported' => $imported,
            'created_parents' => $createdParents,
            'errors_count' => count($errors),
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Импорт завершен',
            'imported' => $imported,
            'created_parent_accounts' => $createdParents,
            'created_parent_emails' => $createdParentEmails,
            'password_setup_required' => $createdParents > 0,
            'password_setup_hint' => $createdParents > 0
                ? 'New parent accounts were created with random passwords. Use the forgot password flow to set first access.'
                : null,
            'errors' => $errors,
        ]);
    }

    public function payments()
    {
        return response()->json($this->buildPaymentsPayload());
    }

    public function exportPayments(): StreamedResponse
    {
        return $this->downloadWorksheet('Payments', 'payments', [
            ['ID', 'Payment ID', 'Request ID', 'Родитель', 'Ребенок', 'Предмет', 'Сумма', 'Валюта', 'Статус', 'Сверка', 'Внешний номер', 'Комментарий', 'Создан', 'Оплачен'],
            ...$this->buildPaymentRows()->map(fn ($row) => [
                (string) $row['id'],
                $row['public_id'],
                $row['request_reference'],
                $row['parent_name'],
                $row['child_name'],
                $row['subject'],
                (string) $row['amount'],
                $row['currency'],
                $row['status'],
                $row['reconciliation_status'],
                $row['external_reference'],
                $row['comment'],
                $row['date'],
                $row['paid_at'],
            ]),
        ]);
    }

    public function importPayments(Request $request, KaspiCsvImportService $importService)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $stats = $importService->import($request->file('file'));

        Log::channel('security')->info('admin.import_payments', [
            'admin_id' => $request->user()?->id,
            'stats' => $stats,
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Импорт платежей завершен.',
            'stats' => $stats,
            ...$this->buildPaymentsPayload(),
        ]);
    }

    public function callbacks()
    {
        return response()->json(
            CallbackRequest::latest()
                ->limit(500)
                ->get()
                ->map(fn (CallbackRequest $callback) => [
                    'id' => $callback->id,
                    'type' => $callback->type ?? 'callback',
                    'name' => $callback->name,
                    'phone' => $callback->phone,
                    'email' => $callback->email,
                    'topic' => $callback->topic,
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
            ->limit(500)
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
                    'elapsed_seconds' => $result->elapsed_seconds,
                    'requires_review' => (bool) $result->requires_review,
                    'review_reasons' => $result->review_reasons ?? [],
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

    protected function buildPaymentsPayload(): array
    {
        return [
            'payments' => $this->buildPaymentRows()->values()->all(),
            'imports' => $this->buildImportRows()->values()->all(),
            'summary' => [
                'new' => PaymentImportRow::where('status', 'new')->count(),
                'matched' => PaymentImportRow::where('status', 'matched')->count(),
                'needs_review' => PaymentImportRow::where('status', 'needs_review')->count(),
            ],
        ];
    }

    protected function buildPaymentRows(): Collection
    {
        return PaymentRecord::with(['parent', 'childProfile', 'subject', 'olympiadRequest'])
            ->latest()
            ->limit(500)
            ->get()
            ->map(fn (PaymentRecord $payment) => [
                'id' => $payment->id,
                'public_id' => $payment->public_id,
                'request_reference' => $payment->olympiadRequest?->public_id,
                'parent_name' => $payment->parent?->name,
                'child_name' => $payment->childProfile?->full_name,
                'subject' => $payment->subject?->name,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'status' => $payment->status,
                'reconciliation_status' => $payment->reconciliation_status,
                'external_reference' => $payment->external_reference,
                'comment' => $payment->comment,
                'date' => optional($payment->created_at)->format('d.m.Y H:i'),
                'paid_at' => optional($payment->paid_at)->format('d.m.Y H:i'),
            ]);
    }

    protected function buildImportRows(): Collection
    {
        return PaymentImportRow::with(['matchedPaymentRecord.olympiadRequest', 'matchedPaymentRecord.childProfile'])
            ->latest()
            ->limit(200)
            ->get()
            ->map(fn (PaymentImportRow $row) => [
                'id' => $row->id,
                'provider' => $row->provider,
                'status' => $row->status,
                'external_reference' => $row->external_reference,
                'amount' => $row->amount,
                'comment' => $row->comment,
                'paid_at' => optional($row->paid_at)->format('d.m.Y H:i'),
                'payment_public_id' => $row->matchedPaymentRecord?->public_id,
                'request_reference' => $row->matchedPaymentRecord?->olympiadRequest?->public_id,
                'child_name' => $row->matchedPaymentRecord?->childProfile?->full_name,
                'date' => optional($row->created_at)->format('d.m.Y H:i'),
            ]);
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
