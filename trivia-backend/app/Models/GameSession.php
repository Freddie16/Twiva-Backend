<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GameSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'session_code',
        'status',
    ];

    // Generate session code before creating
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($session) {
            $session->session_code = Str::random(6); // Simple 6-character code
        });
    }

    // Relationships
    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function players()
    {
        return $this->hasMany(GameSessionPlayer::class);
    }
}