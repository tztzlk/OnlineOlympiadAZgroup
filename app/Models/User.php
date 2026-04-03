<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasPublicId, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'school',
        'city',
        'password',
        'is_admin',
        'plan',
        'settings',
        'public_id',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
        'settings' => 'array',
    ];
    protected $hidden = [
        'id',
        'password',
        'remember_token',
    ];
    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }
    public function olympiadRequests()
    {
        return $this->hasMany(\App\Models\OlympiadRequest::class);
    }

    public function childProfiles()
    {
        return $this->hasMany(ChildProfile::class, 'parent_id')->orderBy('first_name');
    }

    public function quizResults()
    {
        return $this->hasMany(QuizResult::class);
    }

    public function trainingAttempts()
    {
        return $this->hasMany(TrainingAttempt::class, 'parent_id');
    }

    public function paymentRecords()
    {
        return $this->hasMany(PaymentRecord::class, 'parent_id');
    }

    public function platformNotifications()
    {
        return $this->hasMany(PlatformNotification::class)->latest();
    }
}
