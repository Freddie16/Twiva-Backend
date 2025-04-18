<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\Request;
use App\Http\Requests\Api\Game\StoreGameRequest;
use App\Http\Requests\Api\Game\UpdateGameRequest;
use App\Http\Resources\GameResource; // Create this Resource later

class GameController extends Controller
{
    public function index()
    {
        // Fetch games created by the authenticated user
        $games = auth()->user()->createdGames()->with('questions')->get();

        return GameResource::collection($games);
    }

    public function store(StoreGameRequest $request)
    {
        $game = auth()->user()->createdGames()->create($request->validated());

        return new GameResource($game);
    }

    public function show(Game $game)
    {
        // Ensure the user can view this game (either creator or potential player)
        // For simplicity, let's allow the creator to view for now.
        if ($game->user_id !== auth()->id()) {
             // You might want to add logic here to check if the user is a player in an active session
             // For this basic implementation, only creator can see details
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        $game->load('questions.answers'); // Load questions and their answers
        return new GameResource($game);
    }

    public function update(UpdateGameRequest $request, Game $game)
    {
         // Authorization handled by UpdateGameRequest

        $game->update($request->validated());

        // Handle question and answer updates if included in the request
        if ($request->has('questions')) {
             $game->questions()->delete(); // Simple approach: delete and re-create questions
             foreach ($request->questions as $questionData) {
                 $question = $game->questions()->create([
                     'question_text' => $questionData['question_text'],
                     'points' => $questionData['points'] ?? 10,
                 ]);
                 foreach ($questionData['answers'] as $answerData) {
                     $question->answers()->create($answerData);
                 }
             }
        }


        $game->load('questions.answers'); // Reload relationships after update
        return new GameResource($game);
    }

    public function destroy(Game $game)
    {
        // Ensure only the creator can delete the game
        if ($game->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $game->delete();

        return response()->json(['message' => 'Game deleted successfully']);
    }
}