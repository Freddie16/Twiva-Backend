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
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Games
    Route::get('/games', [GameController::class, 'index']); // List all games (or user's games)
    Route::post('/games', [GameController::class, 'store']); // Create a new game
    Route::get('/games/{game}', [GameController::class, 'show']); // View a specific game
    Route::put('/games/{game}', [GameController::class, 'update']); // Update a game
    Route::delete('/games/{game}', [GameController::class, 'destroy']); // Delete a game

    // Questions (nested under games or standalone - standalone for simplicity here)
    Route::post('/games/{game}/questions', [QuestionController::class, 'store']); // Add question to a game
    Route::put('/questions/{question}', [QuestionController::class, 'update']); // Update a question
    Route::delete('/questions/{question}', [QuestionController::class, 'destroy']); // Delete a question

    // Game Sessions
    Route::post('/games/{game}/sessions', [GameSessionController::class, 'store']); // Create a session for a game
    Route::post('/game-sessions/join', [GameSessionController::class, 'join']); // Join a session by code
    Route::get('/game-sessions/{gameSession}', [GameSessionController::class, 'show']); // View a session details
    Route::post('/game-sessions/{gameSession}/start', [GameSessionController::class, 'start']); // Start a session
    Route::post('/game-sessions/{gameSession}/answer', [GameSessionController::class, 'submitAnswer']); // Submit an answer
    Route::post('/game-sessions/{gameSession}/finish', [GameSessionController::class, 'finish']); // Finish a session
    Route::get('/game-sessions/{gameSession}/leaderboard', [GameSessionController::class, 'leaderboard']); // View session leaderboard
});