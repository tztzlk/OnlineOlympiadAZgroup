<?php

// Точка входа Laravel на Vercel (рантайм vercel-php).
// Файловая система функции доступна только для чтения, писать можно лишь в /tmp,
// поэтому кэши фреймворка и storage переносятся туда.

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$tmp = '/tmp/laravel';
foreach (['', '/bootstrap', '/storage/app/public', '/storage/framework/cache/data', '/storage/framework/views', '/storage/framework/sessions', '/storage/logs'] as $dir) {
    is_dir($tmp.$dir) || mkdir($tmp.$dir, 0700, true);
}

foreach ([
    'APP_CONFIG_CACHE' => "$tmp/bootstrap/config.php",
    'APP_EVENTS_CACHE' => "$tmp/bootstrap/events.php",
    'APP_PACKAGES_CACHE' => "$tmp/bootstrap/packages.php",
    'APP_ROUTES_CACHE' => "$tmp/bootstrap/routes.php",
    'APP_SERVICES_CACHE' => "$tmp/bootstrap/services.php",
] as $key => $value) {
    putenv("$key=$value");
    $_ENV[$key] = $_SERVER[$key] = $value;
}

// Vercel вызывает скрипт как /api/index.php, и Symfony принял бы /api за базовый путь
// приложения: /api/subjects превратился бы в /subjects. Приложение живёт в корне домена.
$_SERVER['SCRIPT_NAME'] = $_SERVER['PHP_SELF'] = '/index.php';

require __DIR__.'/../vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->useStoragePath("$tmp/storage");

$app->handleRequest(Request::capture());
