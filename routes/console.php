<?php

use App\Models\User;
use App\Support\DeploymentDatabaseCheckService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('deploy:check-db {--connection= : По умолчанию — подключение из DB_CONNECTION} {--json} {--allow-non-mysql}', function (DeploymentDatabaseCheckService $checker) {
    $result = $checker->inspect(
        connection: (string) ($this->option('connection') ?: config('database.default')),
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

// Создание администратора со случайным стойким паролем. Вместо DatabaseSeeder в продакшене:
// сидер создаёт admin@example.com с паролем «password».
Artisan::command('admin:create {email} {--name=Администратор} {--role=admin : admin | operator | content | analyst}', function () {
    $email = mb_strtolower(trim((string) $this->argument('email')));
    $role = (string) $this->option('role');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $this->error('Некорректный email.');

        return self::FAILURE;
    }

    if (!in_array($role, [User::ADMIN_ROLE_ADMIN, User::ADMIN_ROLE_OPERATOR, User::ADMIN_ROLE_CONTENT, User::ADMIN_ROLE_ANALYST], true)) {
        $this->error('Роль должна быть одной из: admin, operator, content, analyst.');

        return self::FAILURE;
    }

    if (User::where('email', $email)->exists()) {
        $this->error("Пользователь {$email} уже существует.");

        return self::FAILURE;
    }

    // 20 символов из букв, цифр и символов — проходит правила пароля приложения.
    $password = Str::password(20);

    User::create([
        'name' => (string) $this->option('name'),
        'email' => $email,
        'phone' => 'admin-' . Str::lower(Str::random(10)),
        'school' => '—',
        'city' => '—',
        'password' => Hash::make($password),
        'is_admin' => true,
        'admin_role' => $role,
        'plan' => 'free',
    ]);

    $this->info("Администратор создан: {$email}");
    $this->line("Пароль (показывается один раз, сохраните его): {$password}");
    $this->line('Вход: /admin-login');

    return self::SUCCESS;
})->purpose('Create an admin user with a random strong password');
