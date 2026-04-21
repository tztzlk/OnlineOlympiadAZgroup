<?php

namespace App\Http\Controllers\Concerns;

use App\Models\ChildProfile;

trait ResolvesChildId
{
    protected function resolveChildId(int $userId, mixed $childPublicId): ?int
    {
        if (!$childPublicId) {
            return null;
        }

        return ChildProfile::query()
            ->where('parent_id', $userId)
            ->where('public_id', (string) $childPublicId)
            ->value('id');
    }
}
