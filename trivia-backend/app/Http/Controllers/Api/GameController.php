<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Http\Resources\GameResource;
use App\Http\Requests\Api\Game\StoreGameRequest;
use App\Http\Requests\Api\Game\UpdateGameRequest;
use Illuminate\Http\JsonResponse;

class GameController extends Controller
{
    public function index(): JsonResponse
{
    try {
        // Ensure user is authenticated
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        // Load games with questions (using correct relationship)
        $games = auth()->user()->games()->with('questions.answers')->get();

        return response()->json([
            'success' => true,
            'data' => GameResource::collection($games)
        ]);

    } catch (\Exception $e) {
        \Log::error('Failed to fetch games: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to retrieve games',
            'error' => env('APP_DEBUG') ? $e->getMessage() : null
        ], 500);
    }
}

public function store(StoreGameRequest $request)
{
    try {
        $game = auth()->user()->games()->create($request->validated());
        
        return response()->json([
            'success' => true,
            'data' => new GameResource($game)
        ], 201);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to create game',
            'error' => $e->getMessage()
        ], 500);
    }
}
    public function show(Game $game)
{
    if ($game->user_id !== auth()->id()) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized'
        ], 403);
    }
    
    return response()->json([
        'success' => true,
        'data' => new GameResource($game->load('questions.answers'))
    ]);
}
    
public function update(UpdateGameRequest $request, Game $game)
{
    $game->update($request->validated());
    
    return response()->json([
        'success' => true,
        'data' => new GameResource($game)
    ]);
}
    
public function destroy(Game $game)
{
    if ($game->user_id !== auth()->id()) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized'
        ], 403);
    }
    
    $game->delete();
    
    return response()->json([
        'success' => true,
        'message' => 'Game deleted successfully'
    ]);
}
}