<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Import HasApiTokens

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; // Use HasApiTokens

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username', // Add username
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            // Ensure username is set if not provided
            if (empty($user->username)) {
                $user->username = \Illuminate\Support\Str::slug($user->name) . '_' . \Illuminate\Support\Str::random(4);
            }
        });
    }


    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relationships
    public function games()
{
    return $this->hasMany(Game::class);
}

    public function gameSessionPlayers()
    {
        return $this->hasMany(GameSessionPlayer::class);
    }
}