# Локальная база разработки PostgreSQL (без Docker и без пароля суперпользователя postgres).
# Хранится в %LOCALAPPDATA%\Eurika\pgdata, слушает 127.0.0.1:5433.
#
#   powershell -ExecutionPolicy Bypass -File scripts\dev-db.ps1          # создать (при первом запуске) и запустить
#   powershell -ExecutionPolicy Bypass -File scripts\dev-db.ps1 -Stop    # остановить

param(
    [switch]$Stop,
    [string]$PgBin = "C:\Program Files\PostgreSQL\18\bin",
    [int]$Port = 5433
)

$ErrorActionPreference = "Stop"
$root = Split-Path -Parent $PSScriptRoot
$dataDir = Join-Path $env:LOCALAPPDATA "Eurika\pgdata"
$logFile = Join-Path $env:LOCALAPPDATA "Eurika\postgres.log"
$envFile = Join-Path $root ".env"

if (-not (Test-Path "$PgBin\pg_ctl.exe")) { throw "PostgreSQL не найден в $PgBin (укажите -PgBin)" }

if ($Stop) {
    & "$PgBin\pg_ctl.exe" -D $dataDir stop -m fast
    exit 0
}

$fresh = -not (Test-Path (Join-Path $dataDir "PG_VERSION"))
if ($fresh) {
    New-Item -ItemType Directory -Force (Split-Path $dataDir) | Out-Null
    # Суперпользователь кластера доступен только локально и без пароля для служебного скрипта,
    # роль приложения — по паролю (scram-sha-256).
    & "$PgBin\initdb.exe" -D $dataDir -U postgres -E UTF8 --locale=C --auth-local=trust --auth-host=scram-sha-256 | Out-Null
    Add-Content (Join-Path $dataDir "postgresql.conf") "`nlisten_addresses = 'localhost'`nport = $Port`n"
    Set-Content (Join-Path $dataDir "pg_hba.conf") @"
host    all    postgres    127.0.0.1/32    trust
host    all    postgres    ::1/128         trust
host    all    all         127.0.0.1/32    scram-sha-256
host    all    all         ::1/128         scram-sha-256
"@
}

& "$PgBin\pg_ctl.exe" -D $dataDir status *> $null
if ($LASTEXITCODE -ne 0) {
    # Без -Wait: иначе PowerShell ждёт и сам сервер БД, который работает постоянно.
    Start-Process -FilePath "$PgBin\pg_ctl.exe" -ArgumentList @("-D", "`"$dataDir`"", "-l", "`"$logFile`"", "start") -WindowStyle Hidden
    for ($i = 0; $i -lt 30; $i++) {
        Start-Sleep -Seconds 1
        & "$PgBin\pg_isready.exe" -h localhost -p $Port *> $null
        if ($LASTEXITCODE -eq 0) { break }
    }
}

$roleExists = (& "$PgBin\psql.exe" -h localhost -p $Port -U postgres -tAc "select 1 from pg_roles where rolname = 'olympiad_app'") -eq '1'
if (-not $roleExists) {
    $bytes = New-Object byte[] 24
    [System.Security.Cryptography.RandomNumberGenerator]::Create().GetBytes($bytes)
    $password = ([Convert]::ToBase64String($bytes) -replace '[^A-Za-z0-9]', '')
    & "$PgBin\psql.exe" -h localhost -p $Port -U postgres -q -v ON_ERROR_STOP=1 -v app_password="'$password'" -f (Join-Path $root "database\postgres\setup.sql") | Out-Null

    $content = Get-Content $envFile -Raw
    $settings = [ordered]@{ DB_CONNECTION = "pgsql"; DB_HOST = "127.0.0.1"; DB_PORT = "$Port"; DB_DATABASE = "online_olympiad"; DB_USERNAME = "olympiad_app"; DB_PASSWORD = $password }
    foreach ($key in $settings.Keys) {
        $line = "$key=$($settings[$key])"
        if ($content -match "(?m)^$key=.*$") { $content = $content -replace "(?m)^$key=.*$", $line } else { $content = $content.TrimEnd() + "`n$line`n" }
    }
    [System.IO.File]::WriteAllText($envFile, $content, (New-Object System.Text.UTF8Encoding($false)))
    Write-Host "База создана, пароль роли olympiad_app записан в .env" -ForegroundColor Green
}

Write-Host "PostgreSQL для разработки работает: 127.0.0.1:$Port" -ForegroundColor Green
