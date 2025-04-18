<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_session_player_id',
        'question_id',
        'answer_id',
        'is_correct',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    // Relationships
    public function gameSessionPlayer()
    {
        return $this->belongsTo(GameSessionPlayer::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function chosenAnswer()
    {
        return $this->belongsTo(Answer::class, 'answer_id');
    }
}