<?php

namespace App\Providers;

use App\Support\DeploymentSafetyGuard;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        app(DeploymentSafetyGuard::class)->ensureSafeConfiguration();

        if (config('security.enforce_https') && !app()->environment(['local', 'testing'])) {
            URL::forceScheme('https');
        }

        RateLimiter::for('login', fn (Request $request) => [
            Limit::perMinute(5)->by('login:' . $request->ip()),
        ]);

        RateLimiter::for('register', fn (Request $request) => [
            Limit::perHour(3)->by('register:' . $request->ip()),
        ]);

        RateLimiter::for('password-reset-request', fn (Request $request) => [
            Limit::perMinute(3)->by('password-reset-request:' . $request->ip()),
        ]);

        RateLimiter::for('password-reset-confirm', fn (Request $request) => [
            Limit::perMinute(5)->by('password-reset-confirm:' . $request->ip() . ':' . sha1((string) $request->input('email'))),
        ]);

        RateLimiter::for('api-user', fn (Request $request) => [
            Limit::perMinute(100)->by('api:' . ($request->user()?->public_id ?? $request->ip())),
        ]);

        ResetPassword::createUrlUsing(function (object $user, string $token) {
            return rtrim(config('app.url'), '/') . '/reset-password?token=' . $token . '&email=' . urlencode($user->email);
        });
    }
}
