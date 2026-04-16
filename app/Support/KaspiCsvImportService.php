<?php

namespace App\Support;

use App\Models\PaymentImportRow;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;

class KaspiCsvImportService
{
    protected array $aliases = [
        'comment' => ['comment', 'назначение', 'комментарий', 'описание'],
        'amount' => ['amount', 'sum', 'сумма'],
        'paid_at' => ['paid_at', 'date', 'datetime', 'дата', 'дата и время'],
        'external_reference' => ['transaction_id', 'reference', 'номер операции', 'id'],
    ];

    public function __construct(
        protected KaspiPaymentReconciler $reconciler,
    ) {
    }

    public function import(UploadedFile $file): array
    {
        $rows = $this->parseFile($file);
        $createdRows = [];
        $duplicates = 0;

        foreach ($rows as $row) {
            $importRow = PaymentImportRow::query()->firstOrCreate(
                ['fingerprint' => $row['fingerprint']],
                $row
            );

            if ($importRow->wasRecentlyCreated) {
                $createdRows[] = $importRow;
            } else {
                $duplicates++;
            }
        }

        $reconcileStats = $this->reconciler->reconcileImportedRows($createdRows);

        return [
            'processed' => count($rows),
            'imported' => count($createdRows),
            'duplicates' => $duplicates,
            ...$reconcileStats,
        ];
    }

    protected function parseFile(UploadedFile $file): array
    {
        $contents = file_get_contents($file->getRealPath());

        if ($contents === false) {
            return [];
        }

        $contents = $this->normalizeEncoding($contents);
        $contents = preg_replace('/^\xEF\xBB\xBF/', '', $contents) ?? $contents;
        $lines = preg_split('/\r\n|\n|\r/', trim($contents)) ?: [];

        if (count($lines) < 2) {
            return [];
        }

        $delimiter = $this->detectDelimiter($lines[0]);
        $header = array_map([$this, 'normalizeHeader'], str_getcsv(array_shift($lines), $delimiter));
        $rows = [];

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }

            $columns = str_getcsv($line, $delimiter);

            if (count($columns) < count($header)) {
                $columns = array_pad($columns, count($header), null);
            }

            $rawRow = array_combine($header, array_slice($columns, 0, count($header)));

            if ($rawRow === false) {
                continue;
            }

            $rows[] = $this->normalizeRow($rawRow);
        }

        return $rows;
    }

    protected function normalizeRow(array $row): array
    {
        $comment = $this->firstValue($row, 'comment');
        $externalReference = $this->firstValue($row, 'external_reference');
        $amount = $this->normalizeAmount($this->firstValue($row, 'amount'));
        $paidAt = $this->normalizePaidAt($this->firstValue($row, 'paid_at'));

        return [
            'provider' => 'kaspi',
            'fingerprint' => hash('sha256', implode('|', [
                'kaspi',
                $externalReference ?? '',
                $amount ?? '',
                $paidAt?->toISOString() ?? '',
                $comment ?? '',
            ])),
            'external_reference' => $externalReference,
            'amount' => $amount,
            'paid_at' => $paidAt,
            'comment' => $comment,
            'raw_payload' => $row,
            'status' => 'new',
        ];
    }

    protected function firstValue(array $row, string $field): ?string
    {
        foreach ($this->aliases[$field] as $alias) {
            if (!array_key_exists($alias, $row)) {
                continue;
            }

            $value = trim((string) $row[$alias]);

            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    protected function normalizeAmount(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = preg_replace('/[^\d,.\-]/u', '', str_replace(["\xC2\xA0", ' '], '', $value));
        $normalized = str_replace(',', '.', (string) $normalized);

        if ($normalized === '' || !is_numeric($normalized)) {
            return null;
        }

        return number_format((float) $normalized, 2, '.', '');
    }

    protected function normalizePaidAt(?string $value): ?Carbon
    {
        if ($value === null) {
            return null;
        }

        $formats = [
            'd.m.Y H:i:s',
            'd.m.Y H:i',
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'd.m.Y',
            'Y-m-d',
        ];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $value);
            } catch (\Throwable) {
            }
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function normalizeHeader(string $value): string
    {
        return mb_strtolower(trim($value));
    }

    protected function detectDelimiter(string $headerLine): string
    {
        return substr_count($headerLine, ';') >= substr_count($headerLine, ',') ? ';' : ',';
    }

    protected function normalizeEncoding(string $contents): string
    {
        $encoding = mb_detect_encoding($contents, ['UTF-8', 'Windows-1251', 'CP1251'], true) ?: 'UTF-8';

        if ($encoding === 'UTF-8') {
            return $contents;
        }

        return mb_convert_encoding($contents, 'UTF-8', $encoding);
    }
}
