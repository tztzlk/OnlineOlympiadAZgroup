<?php

namespace App\Support;

use App\Models\User;

class OnboardingProgress
{
    public const STEPS = [
        'welcome',
        'child_profile',
        'choose_subject',
        'approval_payment',
        'results_certificate',
    ];

    public static function payloadFor(User $user): array
    {
        $settings = $user->settings ?? [];
        $onboarding = $settings['onboarding'] ?? [];
        $completed = array_values(array_intersect(self::STEPS, $onboarding['completed_steps'] ?? []));
        $dismissed = (bool) ($onboarding['dismissed'] ?? false);

        return [
            'steps' => array_map(fn (string $step, int $index) => [
                'key' => $step,
                'label' => self::labelFor($step),
                'description' => self::descriptionFor($step),
                'completed' => in_array($step, $completed, true),
                'index' => $index + 1,
            ], self::STEPS, array_keys(self::STEPS)),
            'completed_steps' => $completed,
            'current_step' => self::currentStep($completed),
            'progress_percent' => (int) round((count($completed) / count(self::STEPS)) * 100),
            'dismissed' => $dismissed,
            'is_complete' => count($completed) === count(self::STEPS),
        ];
    }

    public static function syncStep(User $user, string $step): void
    {
        if (!in_array($step, self::STEPS, true)) {
            return;
        }

        $settings = $user->settings ?? [];
        $onboarding = $settings['onboarding'] ?? [];
        $completed = $onboarding['completed_steps'] ?? [];

        if (!in_array($step, $completed, true)) {
            $completed[] = $step;
        }

        $settings['onboarding'] = [
            'completed_steps' => array_values(array_intersect(self::STEPS, $completed)),
            'dismissed' => false,
        ];

        $user->forceFill(['settings' => $settings])->save();
    }

    public static function dismiss(User $user): void
    {
        $settings = $user->settings ?? [];
        $onboarding = $settings['onboarding'] ?? [];
        $onboarding['dismissed'] = true;
        $settings['onboarding'] = $onboarding;
        $user->forceFill(['settings' => $settings])->save();
    }

    protected static function currentStep(array $completed): ?string
    {
        foreach (self::STEPS as $step) {
            if (!in_array($step, $completed, true)) {
                return $step;
            }
        }

        return null;
    }

    protected static function labelFor(string $step): string
    {
        return match ($step) {
            'welcome' => 'Как устроена платформа',
            'child_profile' => 'Профиль ребёнка',
            'choose_subject' => 'Выбор олимпиады',
            'approval_payment' => 'Проверка и оплата',
            'results_certificate' => 'Результаты и сертификат',
            default => $step,
        };
    }

    protected static function descriptionFor(string $step): string
    {
        return match ($step) {
            'welcome' => 'Поймите, какие шаги ждут после регистрации.',
            'child_profile' => 'Добавьте ребёнка, чтобы отслеживать заявки и результаты.',
            'choose_subject' => 'Выберите предмет и отправьте заявку на участие.',
            'approval_payment' => 'Дождитесь одобрения, оплаты и доступа к олимпиаде.',
            'results_certificate' => 'Смотрите результаты и скачивайте сертификат в кабинете.',
            default => '',
        };
    }
}
