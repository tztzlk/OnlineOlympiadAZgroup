<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory, HasPublicId;

    protected $fillable = ['name', 'image', 'description', 'start_date', 'public_id'];

    protected $hidden = ['id'];

    protected $casts = [
        'start_date' => 'date',
    ];

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
