<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameSessionPlayer extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_session_id',
        'user_id',
        'score',
    ];

    // Relationships
    public function session()
    {
        return $this->belongsTo(GameSession::class, 'game_session_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function answers()
    {
        return $this->hasMany(PlayerAnswer::class);
    }
}