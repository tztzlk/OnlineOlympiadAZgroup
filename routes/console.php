<?php

use App\Support\DeploymentDatabaseCheckService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('deploy:check-db {--connection=mysql} {--json} {--allow-non-mysql}', function (DeploymentDatabaseCheckService $checker) {
    $result = $checker->inspect(
        connection: (string) $this->option('connection'),
        requireMysql: !$this->option('allow-non-mysql'),
    );

    if ($this->option('json')) {
        $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $result['ready'] ? self::SUCCESS : self::FAILURE;
    }

    $migrationStatus = $result['migrations']['pending'] === []
        ? 'OK'
        : 'PENDING: ' . implode(', ', $result['migrations']['pending']);

    $criticalTablesStatus = $result['missing_tables'] === []
        ? 'OK'
        : 'MISSING: ' . implode(', ', $result['missing_tables']);

    $rows = [
        ['Connection', $result['issues'] === [] ? 'OK' : 'FAIL', sprintf('%s / %s / %s', $result['connection'], $result['driver'] ?: 'unknown', $result['database'] ?: 'unknown')],
        ['Version', $result['version'] ? 'OK' : 'WARN', $result['version'] ?: 'Unable to detect server version'],
        ['Migrations', $result['migrations']['pending'] === [] ? 'OK' : 'FAIL', $migrationStatus],
        ['Critical tables', $result['missing_tables'] === [] ? 'OK' : 'FAIL', $criticalTablesStatus],
    ];

    $this->table(['Check', 'Status', 'Details'], $rows);

    if ($result['tables'] !== []) {
        $this->newLine();
        $this->table(
            ['Table', 'Rows'],
            collect($result['tables'])->map(fn (int $count, string $table) => [$table, (string) $count])->values()->all()
        );
    }

    if ($result['warnings'] !== []) {
        $this->newLine();
        $this->warn('Warnings:');

        foreach ($result['warnings'] as $warning) {
            $this->line('- ' . $warning);
        }
    }

    if ($result['issues'] !== []) {
        $this->newLine();
        $this->error('Deployment database check failed:');

        foreach ($result['issues'] as $issue) {
            $this->line('- ' . $issue);
        }

        return self::FAILURE;
    }

    $this->newLine();
    $this->info('Deployment database check passed.');

    return self::SUCCESS;
})->purpose('Run a read-only production database predeploy check');
