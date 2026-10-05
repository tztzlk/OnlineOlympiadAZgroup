# Запуск сайта локально одной командой: база PostgreSQL, сервер и обработчик очереди.
#   powershell -ExecutionPolicy Bypass -File scripts\dev-start.ps1          # запустить
#   powershell -ExecutionPolicy Bypass -File scripts\dev-start.ps1 -Stop    # остановить всё

param([switch]$Stop, [int]$Port = 8000)

$ErrorActionPreference = "Stop"
$root = Split-Path -Parent $PSScriptRoot
Set-Location $root

# Останавливаем ранее запущенные сервер и очередь этого проекта.
Get-CimInstance Win32_Process -Filter "Name='php.exe'" |
    Where-Object { $_.CommandLine -match 'artisan (serve|queue:work)' -or $_.CommandLine -match 'server\.php' } |
    ForEach-Object { Stop-Process -Id $_.ProcessId -Force -Confirm:$false }

if ($Stop) {
    & "$PSHOME\powershell.exe" -NoProfile -ExecutionPolicy Bypass -File (Join-Path $PSScriptRoot "dev-db.ps1") -Stop
    Write-Host "Сайт остановлен." -ForegroundColor Yellow
    exit 0
}

& "$PSHOME\powershell.exe" -NoProfile -ExecutionPolicy Bypass -File (Join-Path $PSScriptRoot "dev-db.ps1")
php artisan migrate --force | Out-Null

Start-Process -FilePath php -ArgumentList 'artisan', 'serve', '--host=127.0.0.1', "--port=$Port" -WorkingDirectory $root -WindowStyle Hidden
Start-Process -FilePath php -ArgumentList 'artisan', 'queue:work', '--sleep=3', '--tries=3' -WorkingDirectory $root -WindowStyle Hidden

$url = "http://127.0.0.1:$Port"
for ($i = 0; $i -lt 20; $i++) {
    Start-Sleep -Seconds 1
    try { Invoke-WebRequest -UseBasicParsing $url -TimeoutSec 5 | Out-Null; break } catch {}
}

Write-Host "Сайт работает: $url" -ForegroundColor Green
Write-Host "Админка: $url/admin-login" -ForegroundColor Green
Start-Process $url
