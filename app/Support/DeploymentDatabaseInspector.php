<?php

namespace App\Support;

interface DeploymentDatabaseInspector
{
    public function connectionConfig(string $connection): array;

    public function databaseName(string $connection): ?string;

    public function databaseVersion(string $connection): ?string;

    public function hasTable(string $connection, string $table): bool;

    public function tableCount(string $connection, string $table): int;

    /**
     * @return array<int, string>
     */
    public function ranMigrations(string $connection): array;
}
