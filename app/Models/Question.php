<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    public function quizzes()
    {
        return $this->belongsToMany(Quiz::class, 'quiz_question');
    }

    public function options()
    {
        return $this->hasMany(Option::class);
    }
}
