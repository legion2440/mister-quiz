<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = ['completed', 'user_id', 'results', 'xp_earned'];

    protected $casts = [
        'completed' => 'boolean',
        'results' => 'array',
        'xp_earned' => 'integer',
    ];

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'question_quiz', 'quizzes_id', 'question_id')
            ->orderBy('question_quiz.id');
    }

    public function getQuestions()
    {
        return $this->questions()->with('answers')->get();
    }
}
