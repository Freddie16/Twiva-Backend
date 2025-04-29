<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Game;

class CheckGameOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        // Handle both explicit binding and ID resolution
        $game = $request->route('game') ?? 
                Game::find($request->route('game_id') ?? $request->route('game'));

        if (!$game) {
            return response()->json([
                'success' => false,
                'message' => 'Game not found'
            ], 404);
        }

        if ($game->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: You do not own this game'
            ], 403);
        }

        // Inject resolved game into request
        $request->attributes->set('game', $game);
        
        return $next($request);
    }
}