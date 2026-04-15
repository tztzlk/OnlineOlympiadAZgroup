<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;

class QuizResult extends Model
{
    use HasPublicId;

    protected $fillable = [
        'public_id',
        'user_id',
        'child_profile_id',
        'quiz_id',
        'quiz_category_id',
        'score',
        'total',
        'answers',
    ];

    protected $hidden = ['id', 'user_id', 'child_profile_id', 'quiz_id', 'quiz_category_id'];

    protected $casts = [
        'answers' => 'array',
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

    public function childProfile()
    {
        return $this->belongsTo(ChildProfile::class);
    }
 
}
