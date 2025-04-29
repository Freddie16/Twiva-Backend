<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use App\Models\Game;
use App\Models\GameSession;
use App\Models\Question;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->configureRateLimiting();
        $this->configureModelBindings();
        parent::boot();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    protected function configureModelBindings(): void
    {
        Route::model('game', Game::class);
        Route::model('question', Question::class);
        Route::bind('gameSession', fn($value) => GameSession::with(['game', 'players'])->findOrFail($value));
        Route::bind('session', fn($value) => GameSession::with(['game', 'players'])->findOrFail($value));
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}