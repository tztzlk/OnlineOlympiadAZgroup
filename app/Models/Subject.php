<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Subject extends Model
{
    use HasFactory, HasPublicId;

    protected $fillable = ['name', 'image', 'description', 'start_date', 'public_id'];

    protected $hidden = ['id'];

    protected $casts = [
        'start_date' => 'date',
    ];

    public function getImageAttribute($value)
    {
        if (!$value) {
            return null;
        }

        if (Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        return asset('storage/' . ltrim($value, '/'));
    }

    public function olympiadRequests()
    {
        return $this->hasMany(OlympiadRequest::class);
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    public function paymentRecords()
    {
        return $this->hasMany(PaymentRecord::class);
    }
}
