<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\GameSessionController;
use App\Http\Controllers\Api\QuestionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (require authentication)
Route::middleware(['auth:sanctum'])->group(function () {
    // User routes (available to all authenticated users)
    Route::get('/user', fn (Request $r) => $r->user())->name('api.user');
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    
    // Game owner routes
    Route::middleware('game.owner')->group(function() {
        // Game routes (using implicit model binding)
        Route::apiResource('games', GameController::class)->names([
            'index' => 'api.games.index',
            'store' => 'api.games.store',
            'show' => 'api.games.show',
            'update' => 'api.games.update',
            'destroy' => 'api.games.destroy'
        ]);
        
        // Question routes (use {game} instead of {game_id})
        Route::post('/games/{game}/questions', [QuestionController::class, 'store'])
            ->name('api.questions.store');
        Route::put('/questions/{question}', [QuestionController::class, 'update'])
            ->name('api.questions.update');
        Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])
            ->name('api.questions.destroy');
        
        // Session management routes (owner only)
        Route::post('/games/{game}/sessions', [GameSessionController::class, 'store'])
            ->name('api.sessions.store');
        Route::post('/game-sessions/{session}/start', [GameSessionController::class, 'start'])
            ->name('api.sessions.start');
        Route::post('/game-sessions/{session}/finish', [GameSessionController::class, 'finish'])
            ->name('api.sessions.finish');
    });
    
    // Session routes (available to all players)
    Route::post('/game-sessions/join', [GameSessionController::class, 'join'])
        ->name('api.sessions.join');
    Route::get('/game-sessions/{session}', [GameSessionController::class, 'show'])
        ->name('api.sessions.show');
    Route::post('/game-sessions/{gameSession}/answer', [GameSessionController::class, 'submitAnswer'])
        ->name('api.sessions.answer');
    Route::get('/game-sessions/{session}/leaderboard', [GameSessionController::class, 'leaderboard'])
        ->name('api.sessions.leaderboard');
    
    // Test route
    Route::get('/test-session/{gameSession}', function(GameSession $gameSession) {
        return response()->json([
            'session' => $gameSession->id,
            'game' => $gameSession->game,
            'owner' => $gameSession->game->user_id
        ]);
    });
});