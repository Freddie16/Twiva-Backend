<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameSession;
use App\Models\GameSessionPlayer;
use App\Models\PlayerAnswer;
use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Requests\Api\GameSession\JoinGameSessionRequest;
use App\Http\Requests\Api\GameSession\SubmitAnswerRequest;
use App\Http\Resources\GameSessionResource; // Create this Resource later
use App\Http\Resources\GameSessionPlayerResource; // Create this Resource later

class GameSessionController extends Controller
{
    public function store(Game $game)
    {
        // Only the game creator can create a session
        if ($game->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Prevent creating multiple pending/active sessions for the same game
        if ($game->sessions()->whereIn('status', ['waiting', 'active'])->exists()) {
             return response()->json(['message' => 'A session for this game is already active or waiting.'], 409);
        }


        $session = $game->sessions()->create([
            'status' => 'waiting', // Session starts in waiting state
        ]);

        // Add the creator as a player to the session
        $session->players()->create([
            'user_id' => auth()->id(),
            'score' => 0,
        ]);


        return new GameSessionResource($session);
    }

    public function join(JoinGameSessionRequest $request)
    {
        $session = GameSession::where('session_code', $request->session_code)->first();

        if (!$session) {
            return response()->json(['message' => 'Invalid session code'], 404);
        }

        if ($session->status !== 'waiting') {
             return response()->json(['message' => 'Cannot join this session. It is not in a waiting state.'], 400);
        }


        // Prevent joining if already a player in this session
        if ($session->players()->where('user_id', auth()->id())->exists()) {
             return response()->json(['message' => 'You are already in this session.'], 409);
        }


        $player = $session->players()->create([
            'user_id' => auth()->id(),
            'score' => 0,
        ]);

        $session->load('players.user'); // Eager load players and their users
        return new GameSessionResource($session);
    }

    public function show(GameSession $gameSession)
    {
        // Ensure the user is either the creator or a player in the session to view
         if ($gameSession->game->user_id !== auth()->id() && !$gameSession->players()->where('user_id', auth()->id())->exists()) {
             return response()->json(['message' => 'Unauthorized'], 403);
         }

        $gameSession->load('game.questions.answers', 'players.user'); // Load all relevant data
        return new GameSessionResource($gameSession);
    }

    public function start(GameSession $gameSession)
    {
        // Only the session creator can start the game
        if ($gameSession->game->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($gameSession->status !== 'waiting') {
            return response()->json(['message' => 'Session is not in a waiting state.'], 400);
        }

        // Add logic to shuffle questions if desired
        // $gameSession->game->questions->shuffle();

        $gameSession->update(['status' => 'active']);

        $gameSession->load('game.questions.answers', 'players.user');
        return new GameSessionResource($gameSession);
    }

    public function submitAnswer(SubmitAnswerRequest $request, GameSession $gameSession)
    {
         // Authorization handled by SubmitAnswerRequest

        if ($gameSession->status !== 'active') {
             return response()->json(['message' => 'Game session is not active.'], 400);
        }

        $player = $gameSession->players()->where('user_id', auth()->id())->first();
        $questionId = $request->question_id;
        $answerId = $request->answer_id; // Can be null if skipping

        // Ensure the question belongs to the game of this session
        $question = $gameSession->game->questions()->find($questionId);
        if (!$question) {
            return response()->json(['message' => 'Question not found in this game.'], 404);
        }

        // Prevent answering the same question twice in the same session
        if ($player->answers()->where('question_id', $questionId)->exists()) {
            return response()->json(['message' => 'You have already answered this question.'], 409);
        }

        $isCorrect = false;
        if ($answerId) {
             $answer = $question->answers()->find($answerId);
             if (!$answer) {
                 return response()->json(['message' => 'Answer not found for this question.'], 404);
             }
             $isCorrect = $answer->is_correct;

             // Record the player's answer
             PlayerAnswer::create([
                 'game_session_player_id' => $player->id,
                 'question_id' => $questionId,
                 'answer_id' => $answerId,
                 'is_correct' => $isCorrect,
             ]);

             // Update player's score if correct
             if ($isCorrect) {
                 $player->increment('score', $question->points);
             }

        } else {
            // Player skipped the question
             PlayerAnswer::create([
                 'game_session_player_id' => $player->id,
                 'question_id' => $questionId,
                 'answer_id' => null, // Indicates skipped
                 'is_correct' => false,
             ]);
        }


        $player->refresh(); // Refresh the player model to get the updated score

        return response()->json([
             'message' => 'Answer submitted.',
             'is_correct' => $isCorrect,
             'your_score' => $player->score,
        ]);
    }

    public function finish(GameSession $gameSession)
    {
        // Only the session creator can finish the game
        if ($gameSession->game->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($gameSession->status !== 'active') {
            return response()->json(['message' => 'Game session is not active.'], 400);
        }

        $gameSession->update(['status' => 'finished']);

        $gameSession->load('players.user'); // Load players for the final leaderboard
        return new GameSessionResource($gameSession);
    }

    public function leaderboard(GameSession $gameSession)
    {
         // Anyone who was in the session or the creator can view the leaderboard
         if ($gameSession->game->user_id !== auth()->id() && !$gameSession->players()->where('user_id', auth()->id())->exists()) {
             return response()->json(['message' => 'Unauthorized'], 403);
         }

        // Order players by score descending
        $players = $gameSession->players()->with('user')->orderByDesc('score')->get();

        return GameSessionPlayerResource::collection($players); // Use a specific resource for leaderboard
    }
}