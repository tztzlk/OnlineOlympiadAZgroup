<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait HasPublicId
{
    protected static function bootHasPublicId(): void
    {
        static::creating(function ($model) {
            if (empty($model->public_id)) {
                $model->public_id = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    /**
     * Поиск по публичному ID, безопасный для PostgreSQL: там public_id имеет тип uuid,
     * и строка не в формате UUID («1», «abc») роняет запрос. Такой ввод означает «не найдено».
     */
    public function scopeWherePublicId(Builder $query, mixed $value): Builder
    {
        $value = is_string($value) || is_int($value) ? trim((string) $value) : '';

        if (!Str::isUuid($value)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where($this->qualifyColumn('public_id'), $value);
    }

    public function resolveRouteBinding($value, $field = null)
    {
        if (($field === null || $field === 'public_id') && !Str::isUuid((string) $value)) {
            return null;
        }

        return parent::resolveRouteBinding($value, $field);
    }
}
