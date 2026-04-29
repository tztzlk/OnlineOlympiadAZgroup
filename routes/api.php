<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AdminQuizController;
use App\Http\Controllers\Api\QuestionImportController;
use App\Http\Controllers\Api\QuizExportImportController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChildProfileController;
use App\Http\Controllers\Api\LeaderboardController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OlympiadRequestController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\SecurityController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\SupportController;
use App\Http\Controllers\Api\TrainingController;
use App\Http\Controllers\Api\AiController;
use App\Http\Controllers\Api\KaspiCallbackController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Middleware\VerifyKaspiIp;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->middleware(['throttle:register', 'pow:register']);
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:password-reset-request');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:password-reset-confirm');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::post('/auth/admin/login', [AuthController::class, 'adminLogin'])->middleware('throttle:login');

// Kaspi Pay callbacks — GET-based, no auth, IP-restricted + 60 req/min per IP
Route::middleware([VerifyKaspiIp::class, 'throttle:60,1'])->prefix('kaspi')->group(function () {
    Route::get('/check', [KaspiCallbackController::class, 'check']);
    Route::get('/pay', [KaspiCallbackController::class, 'pay']);
});

Route::get('/news', [NewsController::class, 'index']);
Route::get('/leaderboard', [LeaderboardController::class, 'index']);
Route::get('/certificate-check/{result}', [ProfileController::class, 'publicCertificateLookup']);
Route::get('/subjects', [SubjectController::class, 'index']);
Route::get('/subjects/{id}', [SubjectController::class, 'show']);
Route::get('/security/pow-challenge', [SecurityController::class, 'powChallenge']);
Route::post('/support/feedback', [SupportController::class, 'feedback'])->middleware('pow:feedback');
Route::post('/support/callback', [SupportController::class, 'callback'])->middleware('pow:callback');
Route::post('/webhooks/yookassa', [WebhookController::class, 'yookassa'])->middleware('verify.webhook:yookassa');
Route::post('/webhooks/stripe', [WebhookController::class, 'stripe'])->middleware('verify.webhook:stripe');
Route::post('/webhooks/telegram', [WebhookController::class, 'telegram'])->middleware('verify.webhook:telegram');

Route::middleware(['auth:sanctum', 'throttle:api-user', 'admin'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->middleware('admin:dashboard');
        Route::get('/users', [AdminController::class, 'getUsers'])->middleware('admin:dashboard');
        Route::get('/participants', [AdminController::class, 'participants'])->middleware('admin:payments');
        Route::get('/participants/export', [AdminController::class, 'exportParticipants'])->middleware('admin:payments');
        Route::post('/participants/import', [AdminController::class, 'importParticipants'])->middleware('admin:payments');
        Route::get('/users-results', [AdminController::class, 'usersResults'])->middleware('admin:results');
        Route::get('/users-results/export', [AdminController::class, 'exportUsersResults'])->middleware('admin:results');
        Route::get('/payments', [AdminController::class, 'payments'])->middleware('admin:payments');
        Route::get('/payments/export', [AdminController::class, 'exportPayments'])->middleware('admin:payments');
        Route::post('/payments/import', [AdminController::class, 'importPayments'])->middleware('admin:payments');
        Route::get('/callbacks', [AdminController::class, 'callbacks'])->middleware('admin:callbacks');
        Route::get('/callbacks/export', [AdminController::class, 'exportCallbacks'])->middleware('admin:callbacks');

        Route::get('/requests', [OlympiadRequestController::class, 'index'])->middleware('admin:requests');
        Route::get('/requests/stats', [OlympiadRequestController::class, 'stats'])->middleware('admin:requests');
        Route::get('/requests/{olympiadRequest}', [OlympiadRequestController::class, 'show'])->middleware('admin:requests');
        Route::patch('/requests/{olympiadRequest}/status', [OlympiadRequestController::class, 'updateStatus'])->middleware('admin:requests');
        Route::patch('/requests/{olympiadRequest}/payment', [OlympiadRequestController::class, 'updatePaymentStatus'])->middleware('admin:payments');
        Route::delete('/requests/{olympiadRequest}', [OlympiadRequestController::class, 'destroy'])->middleware('admin:requests');

        Route::get('/quizzes', [AdminQuizController::class, 'index'])->middleware('admin:quizzes');
        Route::post('/quizzes', [AdminQuizController::class, 'store'])->middleware('admin:quizzes');
        Route::post('/quizzes/upload-subject-image', [AdminQuizController::class, 'uploadSubjectImage'])->middleware(['admin:quizzes', 'body.limit:10240']);
        Route::post('/quizzes/upload-image', [AdminQuizController::class, 'uploadQuestionImage'])->middleware(['admin:quizzes', 'body.limit:10240']);
        Route::post('/quizzes/parse-document', [QuestionImportController::class, 'parse'])->middleware(['admin:quizzes', 'body.limit:20480']);
        Route::post('/quizzes/import-json', [QuizExportImportController::class, 'importJson'])->middleware(['admin:quizzes', 'body.limit:51200']);
        Route::get('/quizzes/{quiz}/export', [QuizExportImportController::class, 'export'])->middleware('admin:quizzes');
        Route::get('/quizzes/{quiz}', [AdminQuizController::class, 'show'])->middleware('admin:quizzes');
        Route::match(['put', 'patch'], '/quizzes/{quiz}', [AdminQuizController::class, 'update'])->middleware('admin:quizzes');
        Route::delete('/quizzes/{quiz}', [AdminQuizController::class, 'destroy'])->middleware('admin:quizzes');
        Route::post('/quizzes/{quiz}/publish', [AdminQuizController::class, 'publish'])->middleware('admin:quizzes');
        Route::post('/quizzes/{quiz}/unpublish', [AdminQuizController::class, 'unpublish'])->middleware('admin:quizzes');
    });

Route::middleware(['auth:sanctum', 'throttle:api-user'])->group(function () {
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'me']);
        Route::put('/', [ProfileController::class, 'update']);
        Route::get('/children', [ChildProfileController::class, 'index']);
        Route::post('/children', [ChildProfileController::class, 'store']);
        Route::get('/children/{childProfile}', [ChildProfileController::class, 'show']);
        Route::match(['put', 'patch'], '/children/{childProfile}', [ChildProfileController::class, 'update']);
        Route::delete('/children/{childProfile}', [ChildProfileController::class, 'destroy']);
        Route::get('/recent-olympiads', [ProfileController::class, 'recentOlympiads']);
        Route::get('/olympiads', [ProfileController::class, 'olympiads']);
        Route::get('/results', [ProfileController::class, 'myResults']);
        Route::get('/results/{result}/mistakes', [ProfileController::class, 'mistakes']);
        Route::get('/results/{result}/certificate-preview', [ProfileController::class, 'certificatePreview']);
        Route::get('/results/{result}/certificate', [ProfileController::class, 'certificate']);
        Route::get('/payments', [ProfileController::class, 'payments']);
        Route::get('/trainings', [ProfileController::class, 'trainings']);
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead']);
        Route::get('/onboarding', [NotificationController::class, 'onboarding']);
        Route::patch('/onboarding', [NotificationController::class, 'syncOnboarding']);
    });

    Route::prefix('olympiad')->group(function () {
        Route::post('/request', [OlympiadRequestController::class, 'store']);
        Route::get('/request/status', [OlympiadRequestController::class, 'status']);
        Route::post('/request/{olympiadRequest}/payment-report', [OlympiadRequestController::class, 'paymentReport']);
        Route::patch('/request/{olympiadRequest}/status', [OlympiadRequestController::class, 'approveReject']);
    });

    Route::prefix('quiz')->group(function () {
        Route::get('/status/{subjectId}', [QuizController::class, 'getStatus']);
        Route::get('/subjects', [QuizController::class, 'getSubjects']);
        Route::get('/{subjectId}', [QuizController::class, 'getQuiz']);
        Route::post('/{quizId}/start', [QuizController::class, 'startAttempt']);
        Route::post('/{quizId}/submit', [QuizController::class, 'submitQuiz']);
        Route::post('/{quizId}/violate', [QuizController::class, 'violateAttempt']);
    });

    Route::prefix('training')->group(function () {
        Route::get('/{subjectId}', [TrainingController::class, 'getQuiz']);
        Route::post('/{quizId}/submit', [TrainingController::class, 'submit']);
    });

    Route::post('/ai/generate', [AiController::class, 'generate'])->middleware('ai.quota');
});
