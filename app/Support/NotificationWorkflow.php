<?php

namespace App\Support;

use App\Mail\ProductStatusMail;
use App\Models\PlatformNotification;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
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
        array $payload = [],
        bool $sendEmail = false
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

        if ($sendEmail) {
            foreach (self::adminRecipients() as $recipient) {
                self::sendMailToAddress(
                    email: $recipient['email'],
                    recipientName: $recipient['name'],
                    title: $title,
                    body: $body,
                    actionUrl: $actionUrl,
                    payload: $payload,
                );
            }
        }
    }

    protected static function sendMail(User $user, string $title, string $body, ?string $actionUrl, array $payload): void
    {
        self::sendMailToAddress(
            email: $user->email,
            recipientName: $user->name,
            title: $title,
            body: $body,
            actionUrl: $actionUrl,
            payload: $payload,
        );
    }

    protected static function sendMailToAddress(
        string $email,
        string $recipientName,
        string $title,
        string $body,
        ?string $actionUrl,
        array $payload
    ): void {
        try {
            Mail::to($email)->queue(new ProductStatusMail(
                recipientName: $recipientName,
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

    protected static function adminRecipients(): Collection
    {
        $configured = collect(preg_split('/[\s,;]+/', (string) config('services.notifications.admin_emails', ''), -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn (string $email) => [
                'email' => $email,
                'name' => Str::headline(Str::before($email, '@')) ?: 'Администратор',
            ]);

        $admins = User::query()
            ->where(function ($query) {
                $query->where('is_admin', true)->orWhereNotNull('admin_role');
            })
            ->whereNotNull('email')
            ->get(['name', 'email'])
            ->map(fn (User $user) => [
                'email' => (string) $user->email,
                'name' => (string) ($user->name ?: 'Администратор'),
            ]);

        $fallback = collect();
        $supportEmail = (string) config('services.support.email');

        if ($supportEmail !== '') {
            $fallback->push([
                'email' => $supportEmail,
                'name' => 'Администратор',
            ]);
        }

        return $configured
            ->merge($admins)
            ->merge($fallback)
            ->filter(fn (array $recipient) => filter_var($recipient['email'], FILTER_VALIDATE_EMAIL))
            ->unique(fn (array $recipient) => mb_strtolower($recipient['email']))
            ->values();
    }
}
