<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MathQuestion extends Model
{
    protected $table = 'math_questions';
    public $timestamps = false;

    protected $fillable = [
        'question', 'option1', 'option2', 'option3', 'option4', 'answer'
    ];
}
