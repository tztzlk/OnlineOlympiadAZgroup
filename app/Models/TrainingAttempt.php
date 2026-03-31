<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingAttempt extends Model
{
    protected $fillable = [
        'parent_id',
        'child_profile_id',
        'quiz_id',
        'quiz_category_id',
        'score',
        'total',
        'answers',
        'completed_at',
    ];

    protected $casts = [
        'answers' => 'array',
        'completed_at' => 'datetime',
    ];

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function childProfile()
    {
        return $this->belongsTo(ChildProfile::class);
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function category()
    {
        return $this->belongsTo(QuizCategory::class, 'quiz_category_id');
    }
}
