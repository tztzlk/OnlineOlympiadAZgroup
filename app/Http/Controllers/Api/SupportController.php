<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SupportController extends Controller
{
    public function feedback(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:30',
            'topic' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $supportAddress = config('services.support.email') ?: config('mail.from.address');

        if (!$supportAddress) {
            return response()->json([
                'message' => 'Сервис поддержки пока не настроен.',
            ], 503);
        }

        $body = implode("\n", [
            'Новое обращение с сайта',
            'Имя: ' . $data['name'],
            'Email: ' . $data['email'],
            'Телефон: ' . ($data['phone'] ?: 'Не указан'),
            'Тема: ' . $data['topic'],
            '',
            $data['message'],
        ]);

        try {
            Mail::raw($body, function ($message) use ($supportAddress, $data) {
                $message->to($supportAddress)
                    ->replyTo($data['email'], $data['name'])
                    ->subject('Help Desk: ' . $data['topic']);
            });
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Не удалось отправить обращение. Проверьте настройки почты.',
            ], 500);
        }

        return response()->json([
            'message' => 'Обращение отправлено. Мы скоро свяжемся с вами.',
        ]);
    }
}
