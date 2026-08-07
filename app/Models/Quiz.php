<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = ['completed', 'user_id', 'score'];

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'question_quiz', 'quizzes_id', 'question_id');
    }

    public function getQuestions()
    {
        return $this->questions()->with('answers')->get();
    }
}
