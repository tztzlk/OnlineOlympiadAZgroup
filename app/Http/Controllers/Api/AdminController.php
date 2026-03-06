<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'message' => 'Добро пожаловать в админ панель',
            'users_count' => User::count()
        ]);
    }

    public function getUsers()
    {
        return response()->json(User::all());
    }
}