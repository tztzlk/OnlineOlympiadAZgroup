<?php

namespace App\Support;

class StatusPresenter
{
    public static function request(?string $status): array
    {
        return match ($status) {
            'approved' => [
                'key' => 'approved',
                'label' => 'Оформлено',
                'tone' => 'success',
                'icon' => 'check-circle',
                'description' => 'Участие оформлено, можно переходить к оплате или старту.',
                'next_action' => 'Проверьте статус оплаты и доступ к олимпиаде.',
            ],
            'rejected' => [
                'key' => 'rejected',
                'label' => 'Отклонено',
                'tone' => 'danger',
                'icon' => 'x-circle',
                'description' => 'Участие отклонено администратором.',
                'next_action' => 'Проверьте данные участника или свяжитесь с поддержкой.',
            ],
            default => [
                'key' => 'pending',
                'label' => 'Требует проверки',
                'tone' => 'warning',
                'icon' => 'clock',
                'description' => 'Участие ожидает дополнительной проверки.',
                'next_action' => 'Ожидайте решения администратора.',
            ],
        };
    }

    public static function payment(?string $status): array
    {
        return match ($status) {
            'paid' => [
                'key' => 'paid',
                'label' => 'Оплачено',
                'tone' => 'success',
                'icon' => 'wallet',
                'description' => 'Оплата подтверждена.',
                'next_action' => 'Если участие оформлено, можно начинать олимпиаду.',
            ],
            'failed' => [
                'key' => 'failed',
                'label' => 'Ошибка оплаты',
                'tone' => 'danger',
                'icon' => 'alert-circle',
                'description' => 'Платёж не подтверждён или завершился ошибкой.',
                'next_action' => 'Повторите платёж или свяжитесь с поддержкой.',
            ],
            default => [
                'key' => 'pending',
                'label' => 'Ожидает оплаты',
                'tone' => 'warning',
                'icon' => 'credit-card',
                'description' => 'Оплата ещё не подтверждена.',
                'next_action' => 'Оплатите участие и дождитесь подтверждения платежа.',
            ],
        };
    }

    public static function result(?string $status): array
    {
        return match ($status) {
            'passed' => [
                'key' => 'passed',
                'label' => 'Пройдено',
                'tone' => 'success',
                'icon' => 'award',
                'description' => 'Участник успешно прошёл олимпиаду.',
                'next_action' => 'Посмотрите сертификат и сохраните результат.',
            ],
            default => [
                'key' => 'failed',
                'label' => 'Не пройдено',
                'tone' => 'neutral',
                'icon' => 'flag',
                'description' => 'Результат уже опубликован.',
                'next_action' => 'Откройте подробности и посмотрите набранные баллы.',
            ],
        };
    }

    public static function callback(?string $status): array
    {
        return match ($status) {
            'done' => [
                'key' => 'done',
                'label' => 'Обработано',
                'tone' => 'success',
                'icon' => 'check-circle',
                'description' => 'Запрос обработан.',
                'next_action' => 'Проверьте детали обращения.',
            ],
            default => [
                'key' => 'pending',
                'label' => 'Ожидает ответа',
                'tone' => 'warning',
                'icon' => 'message-circle',
                'description' => 'Запрос принят и ожидает обработки.',
                'next_action' => 'Администратор свяжется с вами.',
            ],
        };
    }
}
