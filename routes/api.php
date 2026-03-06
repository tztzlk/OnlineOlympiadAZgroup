<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OlympiadRequestController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\AdminController;

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::post('/logout', [AuthController::class, 'logout'])
        ->middleware('auth:sanctum');

});

/*
|--------------------------------------------------------------------------
| Admin 🚨
|--------------------------------------------------------------------------
*/

Route::post('/auth/admin/login', [AuthController::class, 'adminLogin']);

Route::middleware(['auth:sanctum', 'admin'])
->prefix('admin')
->group(function () {

    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/users', [AdminController::class, 'getUsers']);
 Route::get('/requests', [OlympiadRequestController::class, 'index']);
    Route::get('/requests/stats', [OlympiadRequestController::class, 'stats']);
    Route::get('/requests/{olympiadRequest}', [OlympiadRequestController::class, 'show']);

    Route::patch('/requests/{olympiadRequest}/status',
        [OlympiadRequestController::class, 'updateStatus']
    );

    Route::delete('/requests/{olympiadRequest}',
        [OlympiadRequestController::class, 'destroy']
    );
Route::get('/admin/users-results', [AdminController::class, 'usersResults']);
});
/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/

Route::get('/news', [NewsController::class, 'index']);
Route::get('/subjects', [SubjectController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Protected
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('profile')->group(function () {

        Route::get('/', [ProfileController::class, 'me']);
        Route::get('/recent-olympiads', [ProfileController::class, 'recentOlympiads']);
        Route::get('/olympiads', [ProfileController::class, 'olympiads']);
        Route::get('/results', [ProfileController::class, 'myResults']);

    });

    Route::prefix('olympiad')->group(function () {

        Route::post('/request', [OlympiadRequestController::class, 'store']);
        Route::get('/request/status', [OlympiadRequestController::class, 'status']);
        Route::patch('/request/{id}/status', [OlympiadRequestController::class, 'approveReject']);

    });

    Route::prefix('quiz')->group(function () {

        Route::get('/status/{subjectId}', [QuizController::class, 'getStatus']);
        Route::get('/subjects', [QuizController::class, 'getSubjects']);
        Route::get('/{subjectId}', [QuizController::class, 'getQuiz']);
        Route::post('/{quizId}/submit', [QuizController::class, 'submitQuiz']);

    });

});