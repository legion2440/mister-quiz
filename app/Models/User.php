<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'xp',
        'art',
        'geography',
        'history',
        'science',
        'sports',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function quizes()
    {
        return $this->hasMany(Quiz::class);
    }

    public function getRankAttribute()
    {
        if ($this->xp < 1500) {
            return 'Quiz Aprentice';
        }

        if ($this->xp < 5000) {
            return 'Average Quizer';
        }

        if ($this->xp < 10000) {
            return 'Epic Quizer';
        }

        return 'Quiz Master';
    }

    public function totalCorrectAnswers()
    {
        return collect(['art', 'geography', 'history', 'science', 'sports'])
            ->sum(function ($category) {
                return (int) explode('/', $this->{$category})[0];
            });
    }
}
