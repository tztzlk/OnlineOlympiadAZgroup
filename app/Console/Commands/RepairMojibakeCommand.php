<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RepairMojibakeCommand extends Command
{
    protected $signature = 'db:repair-mojibake
                            {table : Table name}
                            {column : Column name}
                            {--id=id : Primary key column}
                            {--dry-run : Preview affected rows without updating}
                            {--limit=50 : Limit preview rows}';

    protected $description = 'Repair mojibake text stored as latin1-interpreted UTF-8 in MySQL/MariaDB columns.';

    public function handle(): int
    {
        $table = (string) $this->argument('table');
        $column = (string) $this->argument('column');
        $idColumn = (string) $this->option('id');
        $limit = max(1, (int) $this->option('limit'));

        $wrappedColumn = DB::getQueryGrammar()->wrap($column);
        $wrappedId = DB::getQueryGrammar()->wrap($idColumn);
        $conversionSql = "CONVERT(BINARY CONVERT({$wrappedColumn} USING latin1) USING utf8mb4)";
        $likePattern = '%Р%';

        $rows = DB::table($table)
            ->selectRaw("{$wrappedId} as row_id, {$wrappedColumn} as original_value, {$conversionSql} as repaired_value")
            ->whereNotNull($column)
            ->where($column, 'like', $likePattern)
            ->limit($limit)
            ->get();

        if ($rows->isEmpty()) {
            $this->info('Подозрительных mojibake-значений не найдено.');

            return self::SUCCESS;
        }

        $this->info("Найдено подозрительных строк: {$rows->count()} (preview)");

        foreach ($rows as $row) {
            $this->line(sprintf(
                '#%s %s -> %s',
                $row->row_id,
                Str::limit((string) $row->original_value, 80),
                Str::limit((string) $row->repaired_value, 80)
            ));
        }

        if ($this->option('dry-run')) {
            $this->comment('Dry-run mode: изменения не применялись.');

            return self::SUCCESS;
        }

        if (!$this->confirm("Применить перекодировку к {$table}.{$column} для строк, содержащих 'Р'?")) {
            $this->comment('Операция отменена.');

            return self::SUCCESS;
        }

        $affected = DB::table($table)
            ->whereNotNull($column)
            ->where($column, 'like', $likePattern)
            ->update([
                $column => DB::raw($conversionSql),
            ]);

        $this->info("Обновлено строк: {$affected}");

        return self::SUCCESS;
    }
}
