<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CallbackRequest;
use App\Support\NotificationWorkflow;
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

        $requestRecord = CallbackRequest::create([
            'name' => $data['name'],
            'phone' => $data['phone'] ?: 'not_provided',
            'email' => $data['email'],
            'topic' => $data['topic'],
            'message' => $data['message'],
            'type' => 'helpdesk',
            'status' => 'new',
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

        NotificationWorkflow::createForAdmins(
            type: 'new_helpdesk_request',
            title: 'Новое обращение из Help Desk',
            body: "Поступило обращение от {$requestRecord->name}: {$requestRecord->topic}.",
            actionUrl: '/admin/callbacks',
            statusKey: $requestRecord->status
        );

        return response()->json([
            'message' => 'Обращение отправлено. Мы скоро свяжемся с вами.',
        ]);
    }

    public function callback(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:255',
            'message' => 'nullable|string|max:3000',
        ]);

        $callback = CallbackRequest::create([
            ...$data,
            'type' => 'callback',
        ]);

        NotificationWorkflow::createForAdmins(
            type: 'new_callback_request',
            title: 'Новый запрос на обратный звонок',
            body: "Поступил callback-запрос от {$callback->name}.",
            actionUrl: '/admin/callbacks',
            statusKey: $callback->status
        );

        return response()->json([
            'message' => 'Заявка на обратный звонок отправлена.',
            'request' => [
                'id' => $callback->public_id,
                'status' => $callback->status,
            ],
        ], 201);
    }
}
