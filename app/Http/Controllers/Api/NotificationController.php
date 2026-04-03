<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlatformNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $notifications = PlatformNotification::query()
            ->when(
                $user->is_admin,
                fn ($query) => $query->where(function ($inner) use ($user) {
                    $inner->where('for_admin', true)->orWhere('user_id', $user->id);
                }),
                fn ($query) => $query->where('user_id', $user->id)
            )
            ->latest()
            ->limit((int) $request->integer('limit', 20))
            ->get()
            ->map(fn (PlatformNotification $notification) => $this->mapNotification($notification));

        return response()->json([
            'items' => $notifications,
            'unread_count' => $notifications->whereNull('read_at')->count(),
        ]);
    }

    public function markRead(Request $request, PlatformNotification $notification)
    {
        $user = $request->user();

        if ($notification->user_id !== null && $notification->user_id !== $user->id) {
            abort(403);
        }

        if ($notification->for_admin && !$user->is_admin) {
            abort(403);
        }

        if (!$notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        return response()->json([
            'message' => 'Уведомление отмечено как прочитанное.',
            'notification' => $this->mapNotification($notification->fresh()),
        ]);
    }

    public function onboarding(Request $request)
    {
        return response()->json(\App\Support\OnboardingProgress::payloadFor($request->user()));
    }

    public function syncOnboarding(Request $request)
    {
        $data = $request->validate([
            'step' => 'nullable|string',
            'dismissed' => 'nullable|boolean',
        ]);

        if (!empty($data['step'])) {
            \App\Support\OnboardingProgress::syncStep($request->user(), $data['step']);
        }

        if (!empty($data['dismissed'])) {
            \App\Support\OnboardingProgress::dismiss($request->user());
        }

        return response()->json(\App\Support\OnboardingProgress::payloadFor($request->user()->fresh()));
    }

    protected function mapNotification(PlatformNotification $notification): array
    {
        return [
            'id' => $notification->public_id,
            'type' => $notification->type,
            'title' => $notification->title,
            'body' => $notification->body,
            'status_key' => $notification->status_key,
            'action_url' => $notification->action_url,
            'payload' => $notification->payload,
            'read_at' => optional($notification->read_at)->toISOString(),
            'created_at' => optional($notification->created_at)->toISOString(),
            'date' => optional($notification->created_at)->format('d.m.Y H:i'),
        ];
    }
}
