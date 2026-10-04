<?php

namespace App\Support;

/**
 * Маскирование персональных данных перед записью в логи.
 * В журналах должно быть достаточно информации для расследования, но не сами данные.
 */
class Pii
{
    public static function maskEmail(?string $email): ?string
    {
        if (!$email || !str_contains($email, '@')) {
            return $email ? '***' : null;
        }

        [$local, $domain] = explode('@', $email, 2);

        return mb_substr($local, 0, 1) . '***@' . $domain;
    }

    /**
     * Стабильный идентификатор для сопоставления событий в логах без раскрытия адреса.
     */
    public static function fingerprint(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        return substr(hash_hmac('sha256', mb_strtolower(trim($value)), (string) config('app.key')), 0, 16);
    }
}
