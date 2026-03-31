<?php

namespace App\Support;

use RuntimeException;

class DeploymentSafetyGuard
{
    public function ensureSafeConfiguration(): void
    {
        if (!$this->isPreviewDeployment()) {
            return;
        }

        if ($this->isUsingProductionDatabase()) {
            throw new RuntimeException('Preview deployment is configured to use the production database. Aborting boot for safety.');
        }
    }

    public function isPreviewDeployment(): bool
    {
        return strtolower($this->readEnv('VERCEL_ENV')) === 'preview';
    }

    public function isUsingProductionDatabase(): bool
    {
        $databaseUrl = trim($this->readEnv('DATABASE_URL'));
        $productionDatabaseUrl = trim($this->readEnv('PRODUCTION_DATABASE_URL'));

        if ($databaseUrl !== '' && $productionDatabaseUrl !== '' && hash_equals($productionDatabaseUrl, $databaseUrl)) {
            return true;
        }

        $dbHost = strtolower(trim($this->readEnv('DB_HOST')));
        $dbName = strtolower(trim($this->readEnv('DB_DATABASE')));
        $productionDbHost = strtolower(trim($this->readEnv('PRODUCTION_DB_HOST')));
        $productionDbName = strtolower(trim($this->readEnv('PRODUCTION_DB_DATABASE')));

        if ($productionDbHost === '' || $dbHost === '' || $dbHost !== $productionDbHost) {
            return false;
        }

        return $productionDbName === '' || $dbName === $productionDbName;
    }

    protected function readEnv(string $key): string
    {
        $value = getenv($key);

        if ($value !== false) {
            return (string) $value;
        }

        $serverValue = $_SERVER[$key] ?? $_ENV[$key] ?? env($key, '');

        return is_string($serverValue) ? $serverValue : '';
    }
}
