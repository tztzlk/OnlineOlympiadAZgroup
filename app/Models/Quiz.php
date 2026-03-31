<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasPublicId;

    protected $fillable = [
        'subject_id',
        'public_id',
        'title',
        'description',
        'time_limit',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    protected $hidden = ['id'];

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('position');
    }

    public function categories()
    {
        return $this->hasMany(QuizCategory::class)->orderBy('sort_order');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
