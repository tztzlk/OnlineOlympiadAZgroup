<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OlympiadRequest extends Model
{
    use HasFactory, HasPublicId;

    protected $fillable = [
        'public_id',
        'user_id',
        'child_profile_id',
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
        'attempt_started_at',
        'attempt_last_activity_at',
    ];

    protected $casts = [
        'completed' => 'boolean',
        'birth_date' => 'date',
        'paid_at' => 'datetime',
        'disqualified_at' => 'datetime',
        'attempt_started_at' => 'datetime',
        'attempt_last_activity_at' => 'datetime',
    ];

    protected $hidden = ['id', 'user_id', 'child_profile_id', 'subject_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function childProfile()
    {
        return $this->belongsTo(ChildProfile::class);
    }

    public function paymentRecords()
    {
        return $this->hasMany(PaymentRecord::class);
    }

    public function paymentRecord()
    {
        return $this->hasOne(PaymentRecord::class);
    }
}
