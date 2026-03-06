<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OlympiadRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject_id',
        'first_name',
        'last_name',
        'birth_date',
        'grade',
        'language',
        'parent_name',
        'parent_phone',
        'parent_email',
        'status',
        'completed'
    ];



   public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Связь с предметом
   
public function subject()
{
    return $this->belongsTo(\App\Models\Subject::class);
}

}
