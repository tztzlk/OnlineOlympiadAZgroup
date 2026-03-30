<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OlympiadRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject_id',
        'first_name',
        'last_name',
        'birth_date',
        'grade',
        'language',
        'parent_name',
        'parent_phone',
        'parent_email',
        'status',
        'payment_status',
        'paid_at',
        'completed',
        'disqualified_at',
        'disqualification_reason',
    ];

    protected $casts = [
        'completed' => 'boolean',
        'birth_date' => 'date',
        'paid_at' => 'datetime',
        'disqualified_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
