# Создаёт базу online_olympiad и роль olympiad_app, генерирует для неё стойкий пароль
# и записывает его в .env. Пароль суперпользователя postgres спрашивает сам psql —
# он нигде не сохраняется.
#
# Запуск из корня проекта:
#   powershell -ExecutionPolicy Bypass -File scripts\setup-postgres.ps1

param(
    [string]$PsqlPath = "C:\Program Files\PostgreSQL\18\bin\psql.exe",
    [string]$DbHost = "127.0.0.1",
    [int]$Port = 5432
)

$ErrorActionPreference = "Stop"
$root = Split-Path -Parent $PSScriptRoot
$envFile = Join-Path $root ".env"

if (-not (Test-Path $PsqlPath)) { throw "psql не найден: $PsqlPath (укажите -PsqlPath)" }
if (-not (Test-Path $envFile)) { Copy-Item (Join-Path $root ".env.example") $envFile }

# 32 случайных байта -> пароль из букв и цифр (без символов, которые ломают .env и SQL).
$bytes = New-Object byte[] 32
[System.Security.Cryptography.RandomNumberGenerator]::Create().GetBytes($bytes)
$password = ([Convert]::ToBase64String($bytes) -replace '[^A-Za-z0-9]', '').Substring(0, 32)

Write-Host "Введите пароль пользователя postgres, когда psql его спросит." -ForegroundColor Cyan
& $PsqlPath -U postgres -h $DbHost -p $Port -v "app_password='$password'" -f (Join-Path $root "database\postgres\setup.sql")
if ($LASTEXITCODE -ne 0) { throw "psql завершился с ошибкой ($LASTEXITCODE)" }

$content = Get-Content $envFile -Raw
$settings = @{
    DB_CONNECTION = "pgsql"; DB_HOST = $DbHost; DB_PORT = "$Port"
    DB_DATABASE = "online_olympiad"; DB_USERNAME = "olympiad_app"; DB_PASSWORD = $password
}
foreach ($key in $settings.Keys) {
    $line = "$key=$($settings[$key])"
    if ($content -match "(?m)^$key=.*$") { $content = $content -replace "(?m)^$key=.*$", $line }
    else { $content = $content.TrimEnd() + "`r`n$line`r`n" }
}
# Без BOM: иначе Laravel не прочитает первую строку .env.
[System.IO.File]::WriteAllText($envFile, $content, (New-Object System.Text.UTF8Encoding($false)))

Write-Host "Готово. База online_olympiad создана, пароль роли olympiad_app записан в .env." -ForegroundColor Green
Write-Host "Теперь выполните: php artisan migrate --seed" -ForegroundColor Green
