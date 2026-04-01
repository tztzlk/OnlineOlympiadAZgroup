<?php

use App\Support\DeploymentDatabaseInspector;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.production', [
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => 'online_olympiad',
        'username' => 'forge',
        'password' => 'secret',
    ]);
});

it('passes the deployment database preflight for a healthy production mysql connection', function () {
    app()->instance(DeploymentDatabaseInspector::class, new class implements DeploymentDatabaseInspector
    {
        public function connectionConfig(string $connection): array
        {
            return config('database.connections.' . $connection, []);
        }

        public function databaseName(string $connection): ?string
        {
            return 'online_olympiad';
        }

        public function databaseVersion(string $connection): ?string
        {
            return '8.0.36';
        }

        public function hasTable(string $connection, string $table): bool
        {
            return true;
        }

        public function tableCount(string $connection, string $table): int
        {
            return 1;
        }

        public function ranMigrations(string $connection): array
        {
            return collect(glob(database_path('migrations/*.php')) ?: [])
                ->map(fn (string $path) => pathinfo($path, PATHINFO_FILENAME))
                ->sort()
                ->values()
                ->all();
        }
    });

    $this->artisan('deploy:check-db --connection=production')
        ->expectsOutputToContain('Deployment database check passed.')
        ->assertExitCode(0);
});

it('fails the deployment database preflight when migrations or critical tables are missing', function () {
    app()->instance(DeploymentDatabaseInspector::class, new class implements DeploymentDatabaseInspector
    {
        public function connectionConfig(string $connection): array
        {
            return config('database.connections.' . $connection, []);
        }

        public function databaseName(string $connection): ?string
        {
            return 'online_olympiad';
        }

        public function databaseVersion(string $connection): ?string
        {
            return '8.0.36';
        }

        public function hasTable(string $connection, string $table): bool
        {
            return $table !== 'processed_webhooks';
        }

        public function tableCount(string $connection, string $table): int
        {
            return 0;
        }

        public function ranMigrations(string $connection): array
        {
            return collect(glob(database_path('migrations/*.php')) ?: [])
                ->map(fn (string $path) => pathinfo($path, PATHINFO_FILENAME))
                ->reject(fn (string $migration) => $migration === '2026_03_31_000010_create_processed_webhooks_table')
                ->sort()
                ->values()
                ->all();
        }
    });

    $this->artisan('deploy:check-db --connection=production')
        ->expectsOutputToContain('Deployment database check failed:')
        ->expectsOutputToContain('2026_03_31_000010_create_processed_webhooks_table')
        ->expectsOutputToContain('processed_webhooks')
        ->assertExitCode(1);
});

it('fails the deployment database preflight when critical env keys are duplicated', function () {
    app()->instance(DeploymentDatabaseInspector::class, new class implements DeploymentDatabaseInspector
    {
        public function connectionConfig(string $connection): array
        {
            return config('database.connections.' . $connection, []);
        }

        public function databaseName(string $connection): ?string
        {
            return 'online_olympiad';
        }

        public function databaseVersion(string $connection): ?string
        {
            return '8.0.36';
        }

        public function hasTable(string $connection, string $table): bool
        {
            return true;
        }

        public function tableCount(string $connection, string $table): int
        {
            return 1;
        }

        public function ranMigrations(string $connection): array
        {
            return collect(glob(database_path('migrations/*.php')) ?: [])
                ->map(fn (string $path) => pathinfo($path, PATHINFO_FILENAME))
                ->sort()
                ->values()
                ->all();
        }
    });

    $envPath = base_path('.env');
    $backupPath = base_path('.env.testing.backup');

    File::copy($envPath, $backupPath);
    File::put($envPath, implode("\n", [
        'APP_URL=https://example.test',
        'FRONTEND_URL=https://example.test',
        'DB_CONNECTION=mysql',
        'DB_HOST=127.0.0.1',
        'DB_PORT=3306',
        'DB_DATABASE=online_olympiad',
        'DB_USERNAME=forge',
        'SESSION_DRIVER=file',
        'QUEUE_CONNECTION=database',
        'QUEUE_CONNECTION=redis',
        'CACHE_STORE=file',
        'CORS_ALLOWED_ORIGINS=https://example.test',
        'SANCTUM_STATEFUL_DOMAINS=example.test',
        'VITE_API_URL=https://example.test/api',
    ]));

    try {
        $this->artisan('deploy:check-db --connection=production')
            ->expectsOutputToContain('Duplicate QUEUE_CONNECTION entries found in .env')
            ->assertExitCode(1);
    } finally {
        File::move($backupPath, $envPath);
    }
});
