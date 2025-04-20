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
        'code',
        'status'
    ];
    
    protected static function boot()
    {
        parent::boot();
    
        static::creating(function ($session) {
            $session->code = Str::upper(Str::random(6)); // Make it uppercase for better readability
        });
    }
    // Relationships
   // app/Models/GameSession.php
   protected $with = ['game'];

   public function game() // Removed :BelongsTo
   {
       return $this->belongsTo(Game::class);
   }

   public function players()
   {
       return $this->hasMany(GameSessionPlayer::class);
   }
}