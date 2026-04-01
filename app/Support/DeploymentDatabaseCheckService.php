<?php

namespace App\Support;

use Throwable;

class DeploymentDatabaseCheckService
{
    /**
     * @var array<int, string>
     */
    protected array $criticalTables = [
        'cache',
        'sessions',
        'jobs',
        'failed_jobs',
        'personal_access_tokens',
        'processed_webhooks',
        'users',
        'subjects',
        'olympiad_requests',
        'payment_records',
        'quiz_results',
    ];

    /**
     * @var array<int, string>
     */
    protected array $criticalEnvKeys = [
        'APP_URL',
        'FRONTEND_URL',
        'DB_CONNECTION',
        'DB_HOST',
        'DB_PORT',
        'DB_DATABASE',
        'DB_USERNAME',
        'SESSION_DRIVER',
        'CACHE_STORE',
        'QUEUE_CONNECTION',
        'CORS_ALLOWED_ORIGINS',
        'SANCTUM_STATEFUL_DOMAINS',
        'VITE_API_URL',
    ];

    public function __construct(
        protected DeploymentDatabaseInspector $inspector,
        protected DeploymentSafetyGuard $deploymentSafetyGuard,
    ) {
    }

    public function inspect(string $connection = 'mysql', bool $requireMysql = true, ?string $envPath = null): array
    {
        $issues = [];
        $warnings = [];
        $config = $this->inspector->connectionConfig($connection);
        $driver = (string) ($config['driver'] ?? '');
        $defaultConnection = (string) config('database.default', '');
        $envAudit = $this->auditEnvFile($envPath ?? base_path('.env'));

        if ($config === []) {
            $issues[] = "Database connection [{$connection}] is not configured.";
        }

        if ($requireMysql && $driver !== 'mysql') {
            $issues[] = "Connection [{$connection}] must use the mysql driver for production deploys, but [{$driver}] is configured.";
        }

        if ($defaultConnection !== '' && $defaultConnection !== $connection) {
            $warnings[] = "Application default connection is [{$defaultConnection}] while the predeploy check targets [{$connection}].";
        }

        foreach ($envAudit['duplicates'] as $key => $values) {
            $issues[] = "Duplicate {$key} entries found in .env: " . implode(' -> ', $values);
        }

        $envDbConnection = $envAudit['first_values']['DB_CONNECTION'] ?? null;

        if ($envDbConnection === 'sqlite') {
            $warnings[] = 'Local .env points to sqlite; do not reuse it as the production deployment source of truth.';
        }

        if ($requireMysql) {
            foreach (['host' => 'DB_HOST', 'database' => 'DB_DATABASE', 'username' => 'DB_USERNAME'] as $configKey => $envKey) {
                $value = trim((string) ($config[$configKey] ?? ''));

                if ($value === '') {
                    $issues[] = "Connection [{$connection}] is missing {$envKey}.";
                }
            }
        }

        $connectionOk = false;
        $version = null;
        $databaseName = null;
        $ranMigrations = [];
        $pendingMigrations = [];
        $unexpectedMigrations = [];
        $tableSummary = [];
        $missingTables = [];

        if ($issues === []) {
            try {
                $databaseName = $this->inspector->databaseName($connection);
                $version = $this->inspector->databaseVersion($connection);
                $connectionOk = true;
            } catch (Throwable $throwable) {
                $issues[] = "Could not connect to [{$connection}]: {$throwable->getMessage()}";
            }
        }

        if ($connectionOk) {
            if (!$this->inspector->hasTable($connection, 'migrations')) {
                $issues[] = 'The migrations table is missing on the target database.';
            } else {
                $expectedMigrations = $this->expectedMigrations();
                $ranMigrations = $this->inspector->ranMigrations($connection);
                $pendingMigrations = array_values(array_diff($expectedMigrations, $ranMigrations));
                $unexpectedMigrations = array_values(array_diff($ranMigrations, $expectedMigrations));

                if ($pendingMigrations !== []) {
                    $issues[] = 'Pending migrations detected: ' . implode(', ', $pendingMigrations);
                }

                if ($unexpectedMigrations !== []) {
                    $warnings[] = 'Database contains migrations not present in the current codebase: ' . implode(', ', $unexpectedMigrations);
                }
            }

            foreach ($this->criticalTables as $table) {
                if (!$this->inspector->hasTable($connection, $table)) {
                    $missingTables[] = $table;
                    continue;
                }

                $tableSummary[$table] = $this->inspector->tableCount($connection, $table);
            }

            if ($missingTables !== []) {
                $issues[] = 'Critical tables missing: ' . implode(', ', $missingTables);
            }
        }

        if ($this->deploymentSafetyGuard->isPreviewDeployment() && $this->deploymentSafetyGuard->isUsingProductionDatabase()) {
            $issues[] = 'Preview deployment is configured to use the production database.';
        }

        return [
            'ready' => $issues === [],
            'connection' => $connection,
            'driver' => $driver,
            'database' => $databaseName,
            'version' => $version,
            'default_connection' => $defaultConnection,
            'issues' => $issues,
            'warnings' => array_values(array_unique($warnings)),
            'migrations' => [
                'expected' => count($this->expectedMigrations()),
                'ran' => count($ranMigrations),
                'pending' => $pendingMigrations,
                'unexpected' => $unexpectedMigrations,
            ],
            'tables' => $tableSummary,
            'missing_tables' => $missingTables,
            'env_audit' => [
                'duplicates' => $envAudit['duplicates'],
                'db_connection' => $envDbConnection,
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function expectedMigrations(): array
    {
        $files = glob(database_path('migrations/*.php')) ?: [];

        return collect($files)
            ->map(fn (string $path) => pathinfo($path, PATHINFO_FILENAME))
            ->sort()
            ->values()
            ->all();
    }

    protected function auditEnvFile(string $path): array
    {
        if (!is_file($path)) {
            return [
                'duplicates' => [],
                'first_values' => [],
            ];
        }

        $assignments = [];

        foreach (file($path, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (!preg_match('/^([A-Z0-9_]+)\s*=\s*(.*)$/', $line, $matches)) {
                continue;
            }

            $key = $matches[1];

            if (!in_array($key, $this->criticalEnvKeys, true)) {
                continue;
            }

            $assignments[$key][] = $this->normalizeEnvValue($matches[2]);
        }

        $duplicates = [];
        $firstValues = [];

        foreach ($assignments as $key => $values) {
            $firstValues[$key] = $values[0];

            if (count($values) > 1) {
                $duplicates[$key] = $values;
            }
        }

        return [
            'duplicates' => $duplicates,
            'first_values' => $firstValues,
        ];
    }

    protected function normalizeEnvValue(string $value): string
    {
        $value = trim($value);

        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"'))
            || (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            return substr($value, 1, -1);
        }

        return $value;
    }
}
