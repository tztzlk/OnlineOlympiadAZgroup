<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'quiz_completed',
        'quiz_score',
        'is_admin',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'quiz_completed' => 'boolean',
        'is_admin' => 'boolean',
    ];
    protected $hidden = [
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
}

