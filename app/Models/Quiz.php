<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = [
        'subject_id',
        'title',
        'description',
        'time_limit',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('position');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
