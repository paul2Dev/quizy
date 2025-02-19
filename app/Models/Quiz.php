<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quiz extends Model
{
    use HasFactory, SoftDeletes;

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'quiz_question');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'quiz_user')->withTimestamps();
    }
}