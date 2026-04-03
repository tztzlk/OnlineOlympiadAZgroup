<?php

namespace App\Support;

use App\Mail\ProductStatusMail;
use App\Models\PlatformNotification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Throwable;

class NotificationWorkflow
{
    public static function createForUser(
        User $user,
        string $type,
        string $title,
        string $body,
        ?string $actionUrl = null,
        ?string $statusKey = null,
        array $payload = [],
        bool $sendEmail = false
    ): PlatformNotification {
        $notification = PlatformNotification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'action_url' => $actionUrl,
            'status_key' => $statusKey,
            'payload' => $payload,
        ]);

        if ($sendEmail && $user->email) {
            self::sendMail($user, $title, $body, $actionUrl, $payload);
        }

        return $notification;
    }

    public static function createForAdmins(
        string $type,
        string $title,
        string $body,
        ?string $actionUrl = null,
        ?string $statusKey = null,
        array $payload = []
    ): void {
        PlatformNotification::create([
            'for_admin' => true,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'action_url' => $actionUrl,
            'status_key' => $statusKey,
            'payload' => $payload,
        ]);
    }

    protected static function sendMail(User $user, string $title, string $body, ?string $actionUrl, array $payload): void
    {
        try {
            Mail::to($user->email)->send(new ProductStatusMail(
                recipientName: $user->name,
                title: $title,
                body: $body,
                actionUrl: $actionUrl,
                actionLabel: $payload['action_label'] ?? 'Открыть кабинет',
                context: $payload['context'] ?? [],
            ));
        } catch (Throwable $e) {
            report($e);
        }
    }
}
