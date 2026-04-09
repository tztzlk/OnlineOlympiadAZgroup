<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;

class CallbackRequest extends Model
{
    use HasPublicId;

    protected $fillable = [
        'public_id',
        'name',
        'phone',
        'email',
        'topic',
        'message',
        'type',
        'status',
    ];

    protected $hidden = ['id'];
}
