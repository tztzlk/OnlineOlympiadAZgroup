<?php

namespace App\Support;

use App\Models\OlympiadRequest;
use App\Models\QuizResult;

class OlympiadStatusNotifier
{
    public function applicationReceived(OlympiadRequest $request, bool $sendAdminEmail = true): void
    {
        $request->loadMissing(['subject', 'user', 'childProfile']);

        if ($request->user) {
            NotificationWorkflow::createForUser(
                user: $request->user,
                type: 'application_received',
                title: 'Заявка принята',
                body: "Мы приняли заявку на олимпиаду по предмету {$this->subjectName($request)}. Следующий шаг — оплата участия.",
                actionUrl: $this->profileUrl(),
                statusKey: 'approved',
                payload: [
                    'action_label' => 'Открыть кабинет',
                    'context' => $this->requestContext($request),
                ],
                sendEmail: true
            );
        }

        NotificationWorkflow::createForAdmins(
            type: 'new_olympiad_request',
            title: 'Новая заявка на олимпиаду',
            body: "Получена новая заявка: {$this->participantName($request)} / {$this->subjectName($request)}.",
            actionUrl: '/admin/requests',
            statusKey: 'pending',
            payload: [
                'action_label' => 'Открыть заявки',
                'context' => $this->requestContext($request),
            ],
            sendEmail: $sendAdminEmail
        );
    }

    public function paymentStatusChanged(OlympiadRequest $request, string $status, ?string $previousStatus = null, bool $notifyAdmins = true): void
    {
        if ($previousStatus === $status) {
            return;
        }

        $request->loadMissing(['subject', 'user', 'childProfile']);

        if ($request->user) {
            if ($status === 'paid') {
                NotificationWorkflow::createForUser(
                    user: $request->user,
                    type: 'payment_confirmed',
                    title: 'Оплата подтверждена',
                    body: "Мы подтвердили оплату по олимпиаде {$this->subjectName($request)}.",
                    actionUrl: $this->profileUrl(),
                    statusKey: 'paid',
                    payload: [
                        'action_label' => 'Проверить статус',
                        'context' => $this->requestContext($request),
                    ],
                    sendEmail: true
                );

                if ($request->status === 'approved') {
                    $this->accessOpened($request);
                }
            }

            if ($status === 'failed') {
                NotificationWorkflow::createForUser(
                    user: $request->user,
                    type: 'payment_failed',
                    title: 'Оплата не подтверждена',
                    body: "Платёж по олимпиаде {$this->subjectName($request)} пока не удалось подтвердить. Проверьте комментарий к оплате и повторите попытку при необходимости.",
                    actionUrl: $this->profileUrl(),
                    statusKey: 'failed',
                    payload: [
                        'action_label' => 'Открыть кабинет',
                        'context' => $this->requestContext($request),
                    ],
                    sendEmail: true
                );
            }
        }

        if ($notifyAdmins && $status === 'failed') {
            NotificationWorkflow::createForAdmins(
                type: 'payment_review_needed',
                title: 'Требуется проверка оплаты',
                body: "Не удалось автоматически подтвердить платёж по олимпиаде {$this->subjectName($request)}.",
                actionUrl: '/admin/payments',
                statusKey: 'failed',
                payload: [
                    'action_label' => 'Открыть оплаты',
                    'context' => $this->requestContext($request),
                ],
                sendEmail: true
            );
        }
    }

    public function accessOpened(OlympiadRequest $request): void
    {
        $request->loadMissing(['subject', 'user', 'childProfile']);

        if (!$request->user) {
            return;
        }

        NotificationWorkflow::createForUser(
            user: $request->user,
            type: 'access_opened',
            title: 'Доступ открыт',
            body: "Доступ к олимпиаде по предмету {$this->subjectName($request)} уже открыт. Можно переходить к тесту.",
            actionUrl: rtrim(config('app.url'), '/') . '/quiz',
            statusKey: 'ready',
            payload: [
                'action_label' => 'Перейти к тесту',
                'context' => $this->requestContext($request),
            ],
            sendEmail: true
        );
    }

    public function resultsReady(OlympiadRequest $request, QuizResult $result): void
    {
        $request->loadMissing(['subject', 'user', 'childProfile']);

        if (!$request->user) {
            return;
        }

        NotificationWorkflow::createForUser(
            user: $request->user,
            type: 'results_ready',
            title: 'Результаты готовы',
            body: "Результаты олимпиады по предмету {$this->subjectName($request)} уже доступны в кабинете.",
            actionUrl: rtrim(config('app.url'), '/') . '/results',
            statusKey: $this->resultStatus($result),
            payload: [
                'action_label' => 'Смотреть результат',
                'context' => [
                    ...$this->requestContext($request),
                    'Результат' => "{$result->score} из {$result->total}",
                ],
            ],
            sendEmail: true
        );
    }

    protected function requestContext(OlympiadRequest $request): array
    {
        return [
            'Участник' => $this->participantName($request),
            'Предмет' => $this->subjectName($request),
            'Request ID' => $request->public_id,
            'Комментарий к оплате' => trim("Заявка {$request->public_id}"),
        ];
    }

    protected function participantName(OlympiadRequest $request): string
    {
        return $request->childProfile?->full_name
            ?? trim($request->first_name . ' ' . $request->last_name)
            ?: 'Участник';
    }

    protected function subjectName(OlympiadRequest $request): string
    {
        return $request->subject?->name ?? 'олимпиада';
    }

    protected function profileUrl(): string
    {
        return rtrim(config('app.url'), '/') . '/profile';
    }

    protected function resultStatus(QuizResult $result): string
    {
        $percent = $result->total > 0 ? (int) round(($result->score / $result->total) * 100) : 0;

        return $percent >= 60 ? 'passed' : 'failed';
    }
}
