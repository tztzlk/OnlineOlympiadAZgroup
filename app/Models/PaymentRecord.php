<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;

class PaymentRecord extends Model
{
    use HasPublicId;

    protected $fillable = [
        'public_id',
        'parent_id',
        'child_profile_id',
        'subject_id',
        'olympiad_request_id',
        'amount',
        'currency',
        'status',
        'external_reference',
        'comment',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    protected $hidden = ['id', 'parent_id', 'child_profile_id', 'subject_id', 'olympiad_request_id'];

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function childProfile()
    {
        return $this->belongsTo(ChildProfile::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function olympiadRequest()
    {
        return $this->belongsTo(OlympiadRequest::class);
    }
}
