<?php

use App\Http\Controllers\PublicPageController;
use Illuminate\Support\Facades\Route;

Route::get('/robots.txt', [PublicPageController::class, 'robots']);
Route::get('/sitemap.xml', [PublicPageController::class, 'sitemap']);

Route::get('/', [PublicPageController::class, 'page']);
Route::get('/subject', [PublicPageController::class, 'page']);
Route::get('/subjects/{subject}', [PublicPageController::class, 'subject']);
Route::get('/about', [PublicPageController::class, 'page']);
Route::get('/rules', [PublicPageController::class, 'page']);
Route::get('/leaderboard', [PublicPageController::class, 'page']);
Route::get('/certificate-check', [PublicPageController::class, 'page']);
Route::get('/help-desk', [PublicPageController::class, 'page']);

Route::get('/{any}', [PublicPageController::class, 'spaFallback'])
    ->where('any', '.*');
