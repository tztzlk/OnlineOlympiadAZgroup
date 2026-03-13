<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'image', 'description', 'start_date'];

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
}
