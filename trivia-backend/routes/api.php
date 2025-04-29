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
    // User routes
    Route::get('/user', fn (Request $r) => $r->user())->name('api.user');
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    
    // Game routes - index doesn't need ownership check
    Route::get('/games', [GameController::class, 'index'])->name('api.games.index');
    
    // Game owner routes (only for operations that need ownership verification)
    Route::middleware('game.owner')->group(function() {
        // Game CRUD routes (except index)
        Route::post('/games', [GameController::class, 'store'])->name('api.games.store');
        Route::get('/games/{game}', [GameController::class, 'show'])->name('api.games.show');
        Route::put('/games/{game}', [GameController::class, 'update'])->name('api.games.update');
        Route::delete('/games/{game}', [GameController::class, 'destroy'])->name('api.games.destroy');
        
        // Question routes
        Route::post('/games/{game}/questions', [QuestionController::class, 'store'])
            ->name('api.questions.store');
        Route::put('/questions/{question}', [QuestionController::class, 'update'])
            ->name('api.questions.update');
        Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])
            ->name('api.questions.destroy');
        
        // Session management
        Route::post('/games/{game}/sessions', [GameSessionController::class, 'store'])
            ->name('api.sessions.store');
        Route::post('/game-sessions/{session}/start', [GameSessionController::class, 'start'])
            ->name('api.sessions.start');
        Route::post('/game-sessions/{session}/finish', [GameSessionController::class, 'finish'])
            ->name('api.sessions.finish');
    });
    
    // Player session routes
    Route::post('/game-sessions/join', [GameSessionController::class, 'join'])
        ->name('api.sessions.join');
    Route::get('/game-sessions/{session}', [GameSessionController::class, 'show'])
        ->name('api.sessions.show');
    Route::post('/game-sessions/{gameSession}/answer', [GameSessionController::class, 'submitAnswer'])
        ->name('api.sessions.answer');
    Route::get('/game-sessions/{session}/leaderboard', [GameSessionController::class, 'leaderboard'])
        ->name('api.sessions.leaderboard');
});
    
    // Test route
    Route::get('/test-session/{gameSession}', function(GameSession $gameSession) {
        return response()->json([
            'session' => $gameSession->id,
            'game' => $gameSession->game,
            'owner' => $gameSession->game->user_id
        ]);
    });
