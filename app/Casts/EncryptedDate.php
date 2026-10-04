<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * Дата (например, дата рождения ребёнка), хранящаяся в БД зашифрованной.
 * Наружу отдаётся как Carbon — так же, как обычный каст 'date'.
 */
class EncryptedDate implements CastsAttributes
{
    protected EncryptedString $strings;

    public function __construct()
    {
        $this->strings = new EncryptedString();
    }

    public function get(Model $model, string $key, mixed $value, array $attributes): ?Carbon
    {
        $plain = $this->strings->get($model, $key, $value, $attributes);

        if ($plain === null || $plain === '') {
            return null;
        }

        try {
            return Carbon::parse($plain)->startOfDay();
        } catch (Throwable) {
            return null;
        }
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $date = $value instanceof \DateTimeInterface
            ? Carbon::instance($value)
            : Carbon::parse((string) $value);

        return $this->strings->set($model, $key, $date->toDateString(), $attributes);
    }
}
