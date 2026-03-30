<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizResult extends Model
{
    protected $fillable = [
        'user_id',
        'quiz_id',
        'quiz_category_id',
        'score',
        'total'
    ];

    /*
    |--------------------------------------------------
    | Relations
    |--------------------------------------------------
    */

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function category()
    {
        return $this->belongsTo(QuizCategory::class, 'quiz_category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
 
}
