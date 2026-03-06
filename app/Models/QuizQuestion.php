<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model

{
    protected $fillable = [
        'question',
        'subject',
        'options',
        'answer'
    ];

    protected $casts = [
    'options' => 'array',
];
}
