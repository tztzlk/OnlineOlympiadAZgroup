<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizCategory extends Model
{
    protected $fillable = [
        'quiz_id',
        'label',
        'grade_from',
        'grade_to',
        'sort_order',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('position');
    }
}
