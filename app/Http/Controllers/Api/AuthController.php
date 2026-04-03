<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\NotificationWorkflow;
use App\Support\OnboardingProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Throwable;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20|unique:users',
            'school' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'password' => ['required', 'string', 'confirmed', PasswordRule::min(12)->letters()->mixedCase()->numbers()->symbols()],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->string('name')->trim()->value(),
            'email' => $request->string('email')->trim()->lower()->value(),
            'phone' => $request->string('phone')->value(),
            'school' => $request->string('school')->trim()->value(),
            'city' => $request->string('city')->trim()->value(),
            'password' => Hash::make($request->string('password')->value()),
            'plan' => 'free',
            'settings' => [
                'onboarding' => [
                    'completed_steps' => ['welcome'],
                    'dismissed' => false,
                ],
            ],
        ]);

        OnboardingProgress::syncStep($user, 'welcome');

        NotificationWorkflow::createForUser(
            user: $user,
            type: 'registration_completed',
            title: 'Регистрация завершена',
            body: 'Аккаунт создан. Теперь добавьте ребёнка, выберите олимпиаду и отслеживайте статус участия в кабинете.',
            actionUrl: rtrim(config('app.url'), '/') . '/profile',
            statusKey: 'welcome',
            payload: [
                'action_label' => 'Открыть кабинет',
                'context' => [
                    'Следующий шаг' => 'Добавить профиль ребёнка',
                ],
            ],
            sendEmail: true
        );

        NotificationWorkflow::createForAdmins(
            type: 'new_user_registered',
            title: 'Новый пользователь зарегистрирован',
            body: "Пользователь {$user->name} ({$user->email}) создал аккаунт на платформе.",
            actionUrl: '/admin',
            statusKey: 'new_user'
        );

        Log::channel('security')->info('auth.register_success', [
            'event' => 'auth.register_success',
            'user_public_id' => $user->public_id,
            'email' => $user->email,
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Пользователь успешно зарегистрирован.',
            'user' => $user,
            'token' => $user->createToken('auth_token')->plainTextToken,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $email = $request->string('email')->lower()->value();
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($request->string('password')->value(), $user->password)) {
            Log::channel('security')->warning('auth.login_failed', [
                'event' => 'auth.login_failed',
                'email' => $email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'guard' => 'user',
            ]);

            return response()->json(['message' => 'Неверный email или пароль.'], 401);
        }

        if ($user->is_admin) {
            Log::channel('security')->warning('auth.login_blocked_admin_endpoint_mismatch', [
                'event' => 'auth.login_blocked_admin_endpoint_mismatch',
                'user_public_id' => $user->public_id,
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Администратор должен входить через отдельную форму admin login.',
            ], 403);
        }

        $user->tokens()->delete();

        Log::channel('security')->info('auth.login_success', [
            'event' => 'auth.login_success',
            'user_public_id' => $user->public_id,
            'email' => $user->email,
            'ip' => $request->ip(),
            'guard' => 'user',
        ]);

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('auth_token')->plainTextToken,
        ]);
    }

    public function adminLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $email = $request->string('email')->lower()->value();
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($request->string('password')->value(), $user->password)) {
            Log::channel('security')->warning('auth.admin_login_failed', [
                'event' => 'auth.admin_login_failed',
                'email' => $email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'guard' => 'admin',
            ]);

            return response()->json(['message' => 'Неверный email или пароль.'], 401);
        }

        if (!$user->is_admin) {
            Log::channel('security')->warning('auth.admin_login_forbidden', [
                'event' => 'auth.admin_login_forbidden',
                'user_public_id' => $user->public_id,
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);

            return response()->json(['message' => 'Доступ открыт только для администратора.'], 403);
        }

        $user->tokens()->delete();

        Log::channel('security')->info('auth.admin_login_success', [
            'event' => 'auth.admin_login_success',
            'user_public_id' => $user->public_id,
            'email' => $user->email,
            'ip' => $request->ip(),
            'guard' => 'admin',
        ]);

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('admin-token')->plainTextToken,
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            Password::sendResetLink($request->only('email'));
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Не удалось отправить письмо. Проверьте настройки почты.',
            ], 500);
        }

        return response()->json([
            'message' => 'Если аккаунт с таким email существует, ссылка для восстановления уже отправлена.',
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => ['required', 'string', 'confirmed', PasswordRule::min(12)->letters()->mixedCase()->numbers()->symbols()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();

                $user->tokens()->delete();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'message' => __($status),
            ], 422);
        }

        return response()->json([
            'message' => 'Пароль успешно обновлён. Теперь можно войти.',
        ]);
    }

    public function profile(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        Log::channel('security')->info('auth.logout', [
            'event' => 'auth.logout',
            'user_public_id' => $request->user()?->public_id,
            'ip' => $request->ip(),
        ]);

        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Вы вышли из системы.']);
    }
}
