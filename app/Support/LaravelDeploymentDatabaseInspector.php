<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaravelDeploymentDatabaseInspector implements DeploymentDatabaseInspector
{
    public function connectionConfig(string $connection): array
    {
        return config('database.connections.' . $connection, []);
    }

    public function databaseName(string $connection): ?string
    {
        return DB::connection($connection)->getDatabaseName();
    }

    public function databaseVersion(string $connection): ?string
    {
        $driver = $this->connectionConfig($connection)['driver'] ?? null;

        $query = match ($driver) {
            'mysql' => 'select version() as version',
            'pgsql' => 'select version() as version',
            'sqlite' => 'select sqlite_version() as version',
            default => null,
        };

        if ($query === null) {
            return null;
        }

        $result = DB::connection($connection)->selectOne($query);

        return isset($result->version) ? (string) $result->version : null;
    }

    public function hasTable(string $connection, string $table): bool
    {
        return Schema::connection($connection)->hasTable($table);
    }

    public function tableCount(string $connection, string $table): int
    {
        return DB::connection($connection)->table($table)->count();
    }

    public function ranMigrations(string $connection): array
    {
        return DB::connection($connection)
            ->table('migrations')
            ->orderBy('migration')
            ->pluck('migration')
            ->map(fn ($migration) => (string) $migration)
            ->all();
    }
}
