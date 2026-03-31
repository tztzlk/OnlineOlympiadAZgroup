<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;

class ChildProfile extends Model
{
    use HasPublicId;

    protected $fillable = [
        'public_id',
        'parent_id',
        'first_name',
        'last_name',
        'birth_date',
        'grade',
        'school',
        'city',
        'language_preference',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    protected $hidden = ['id', 'parent_id'];

    protected $appends = [
        'full_name',
    ];

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function olympiadRequests()
    {
        return $this->hasMany(OlympiadRequest::class);
    }

    public function quizResults()
    {
        return $this->hasMany(QuizResult::class);
    }

    public function trainingAttempts()
    {
        return $this->hasMany(TrainingAttempt::class);
    }

    public function paymentRecords()
    {
        return $this->hasMany(PaymentRecord::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}
