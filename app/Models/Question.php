<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'quiz_id',
        'quiz_category_id',
        'question',
        'image_source',
        'image_url',
        'image_path',
        'position',
    ];

    protected $appends = [
        'image',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function category()
    {
        return $this->belongsTo(QuizCategory::class, 'quiz_category_id');
    }

    public function answers()
    {
        return $this->hasMany(Answer::class)->orderBy('position');
    }

    public function getImageAttribute(): ?string
    {
        if ($this->image_source === 'upload' && $this->image_path) {
            return asset('storage/' . ltrim($this->image_path, '/'));
        }

        return $this->image_url;
    }
}
