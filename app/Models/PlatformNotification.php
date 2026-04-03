<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;

class PlatformNotification extends Model
{
    use HasPublicId;

    protected $fillable = [
        'public_id',
        'user_id',
        'for_admin',
        'type',
        'title',
        'body',
        'status_key',
        'action_url',
        'payload',
        'read_at',
    ];

    protected $casts = [
        'for_admin' => 'boolean',
        'payload' => 'array',
        'read_at' => 'datetime',
    ];

    protected $hidden = ['id', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
