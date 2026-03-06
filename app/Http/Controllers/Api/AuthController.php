<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Регистрация
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
          'phone' => 'required|string|max:20|unique:users',

            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
              'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Пользователь успешно зарегистрирован',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    // Логин
   public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Неверный email или пароль'], 401);
    }

    // 🚨 Если пользователь — админ → нельзя входить через обычный login
    if ($user->is_admin == 1) {
        return response()->json([
            'message' => 'Администратор должен входить через admin login'
        ], 403);
    }

    $user->tokens()->delete();

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'user' => $user,
        'token' => $token
    ]);
}
public function adminLogin(Request $request)
{
    $credentials = $request->only('email', 'password');

    if (!$user = User::where('email', $request->email)->first()) {
        return response()->json(['message' => 'Неверный email или пароль'], 401);
    }

    if (!Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Неверный email или пароль'], 401);
    }

    // ✅ ВОТ ТАК
    if ((int)$user->is_admin !== 1) {
        return response()->json(['message' => 'Доступ только для администратора'], 403);
    }

    $token = $user->createToken('admin-token')->plainTextToken;

    return response()->json([
        'user' => $user,
        'token' => $token
    ]);
}
    // Профиль
    public function profile(Request $request)
    {
        return response()->json($request->user());
    }

    // Логаут
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Вы вышли из системы']);
    }
}
