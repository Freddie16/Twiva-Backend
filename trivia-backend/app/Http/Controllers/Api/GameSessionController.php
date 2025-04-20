<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameSession;
use App\Models\GameSessionPlayer;
use App\Models\PlayerAnswer;
use App\Models\Answer;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use App\Http\Requests\Api\GameSession\JoinGameSessionRequest;
use App\Http\Requests\Api\GameSession\SubmitAnswerRequest;
use App\Http\Resources\GameSessionResource;
use App\Http\Resources\GameSessionPlayerResource;

class GameSessionController extends Controller
{
    public function store(Request $request, Game $game)
    {
        try {
            if ($game->user_id !== auth()->id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            
            $session = $game->sessions()->create([
                'status' => 'waiting'
            ]);
            
            return response()->json([
                'success' => true,
                'session_id' => $session->id,
                'code' => $session->code
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create game session', [
                'error' => $e->getMessage(),
                'game_id' => $game->id,
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create session',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function join(JoinGameSessionRequest $request)
    {
        $session = GameSession::where('code', $request->session_code)->first();

        if (!$session) {
            return response()->json(['message' => 'Invalid session code'], 404);
        }

        if ($session->status !== 'waiting') {
            return response()->json([
                'message' => 'Cannot join this session. It is not in a waiting state.',
                'current_status' => $session->status
            ], 400);
        }

        if ($session->players()->where('user_id', auth()->id())->exists()) {
            return response()->json(['message' => 'You are already in this session.'], 409);
        }

        try {
            $player = $session->players()->create([
                'user_id' => auth()->id(),
                'score' => 0,
            ]);

            $session->load('players.user');
            return new GameSessionResource($session);

        } catch (\Exception $e) {
            Log::error('Failed to join game session', [
                'error' => $e->getMessage(),
                'session_id' => $session->id,
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'message' => 'Failed to join session',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(GameSession $gameSession)
    {
        if ($gameSession->game->user_id !== auth()->id() && 
            !$gameSession->players()->where('user_id', auth()->id())->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $gameSession->load('game.questions.answers', 'players.user');
        return new GameSessionResource($gameSession);
    }

    public function start($id)
    {
        try {
            $gameSession = GameSession::with(['game', 'players'])->findOrFail($id);

            // Verify authentication and ownership
            if ($gameSession->game->user_id !== auth()->id()) {
                return response()->json([
                    'message' => 'Unauthorized - You must be the game owner',
                    'required_owner_id' => $gameSession->game->user_id,
                    'your_user_id' => auth()->id()
                ], 403);
            }

            // Validate session status
            if ($gameSession->status !== 'waiting') {
                return response()->json([
                    'message' => 'Session must be in waiting status',
                    'current_status' => $gameSession->status
                ], 400);
            }

            // Check minimum players
            if ($gameSession->players->count() < 1) {
                return response()->json([
                    'message' => 'Cannot start session with no players',
                    'player_count' => $gameSession->players->count()
                ], 400);
            }

            // Update session status
            $gameSession->update(['status' => 'active']);

            // Load relationships for response
            $gameSession->load(['game.questions.answers', 'players.user']);

            return response()->json([
                'success' => true,
                'message' => 'Game session started successfully',
                'session' => new GameSessionResource($gameSession)
            ]);

        } catch (\Exception $e) {
            Log::error('Start session failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'session_id' => $id,
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'message' => 'Failed to start game session',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function submitAnswer(SubmitAnswerRequest $request, GameSession $gameSession)
    {
        try {
            Log::info('SubmitAnswer Request:', [
                'user_id' => auth()->id(),
                'session_id' => $gameSession->id,
                'data' => $request->all()
            ]);

            // Check if game session is active
            if ($gameSession->status !== 'active') {
                return response()->json([
                    'message' => 'Game session is not active',
                    'current_status' => $gameSession->status
                ], 400);
            }

            // Verify player exists in session
            $player = $gameSession->players()
                        ->where('user_id', auth()->id())
                        ->first();
            
            if (!$player) {
                return response()->json([
                    'message' => 'You are not a player in this session',
                    'user_id' => auth()->id(),
                    'session_id' => $gameSession->id
                ], 403);
            }

            $questionId = $request->question_id;
            $answerId = $request->answer_id;

            // Verify question belongs to this game
            $question = $gameSession->game->questions()->find($questionId);
            if (!$question) {
                return response()->json([
                    'message' => 'Question not found in this game',
                    'question_id' => $questionId
                ], 404);
            }

            // Prevent duplicate answers
            if ($player->answers()->where('question_id', $questionId)->exists()) {
                return response()->json([
                    'message' => 'You have already answered this question',
                    'question_id' => $questionId
                ], 409);
            }

            $isCorrect = false;
            $pointsAwarded = 0;

            if ($answerId) {
                // Verify answer belongs to this question
                $answer = $question->answers()->find($answerId);
                if (!$answer) {
                    return response()->json([
                        'message' => 'Answer not found for this question',
                        'answer_id' => $answerId
                    ], 404);
                }

                $isCorrect = $answer->is_correct;
                $pointsAwarded = $isCorrect ? $question->points : 0;

                // Create player answer record
                PlayerAnswer::create([
                    'game_session_player_id' => $player->id,
                    'question_id' => $questionId,
                    'answer_id' => $answerId,
                    'is_correct' => $isCorrect,
                ]);

                if ($isCorrect) {
                    $player->increment('score', $pointsAwarded);
                }
            } else {
                // Handle skipped question
                PlayerAnswer::create([
                    'game_session_player_id' => $player->id,
                    'question_id' => $questionId,
                    'answer_id' => null,
                    'is_correct' => false,
                ]);
            }

            return response()->json([
                'message' => 'Answer submitted successfully',
                'is_correct' => $isCorrect,
                'points_awarded' => $pointsAwarded,
                'current_score' => $player->fresh()->score
            ]);

        } catch (QueryException $e) {
            Log::error('Database error in submitAnswer', [
                'error' => $e->getMessage(),
                'session' => $gameSession->id,
                'user' => auth()->id(),
                'input' => $request->all()
            ]);
            return response()->json([
                'message' => 'Database error occurred',
                'error' => $e->getMessage()
            ], 500);

        } catch (\Exception $e) {
            Log::error('Answer submission failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'session' => $gameSession->id ?? null,
                'user' => auth()->id(),
                'input' => $request->all()
            ]);
            return response()->json([
                'message' => 'Failed to submit answer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function finish($id)
{
    try {
        $gameSession = GameSession::with('game')->findOrFail($id);
        
        if (!$gameSession->game) {
            return response()->json([
                'message' => 'Associated game not found',
                'session_id' => $gameSession->id
            ], 404);
        }

        if ($gameSession->game->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized - You must be the game owner'
            ], 403);
        }

        if ($gameSession->status !== 'active') {
            return response()->json([
                'message' => 'Game session is not active',
                'current_status' => $gameSession->status
            ], 400);
        }

        $gameSession->update(['status' => 'finished']);

        return response()->json([
            'success' => true,
            'message' => 'Game session finished successfully',
            'session' => new GameSessionResource($gameSession->load('players.user'))
        ]);

    } catch (\Exception $e) {
        \Log::error('Finish session failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'message' => 'Failed to finish game session',
            'error' => $e->getMessage()
        ], 500);
    }
}
public function leaderboard($id)
{
    try {
        // Load the game session with necessary relationships
        $gameSession = GameSession::with(['game', 'players.user'])->findOrFail($id);

        // Check if game exists
        if (!$gameSession->game) {
            return response()->json([
                'message' => 'Associated game not found',
                'session_id' => $gameSession->id
            ], 404);
        }

        // Verify access - either owner or player
        $isOwner = $gameSession->game->user_id === auth()->id();
        $isPlayer = $gameSession->players->contains('user_id', auth()->id());
        
        if (!$isOwner && !$isPlayer) {
            return response()->json([
                'message' => 'Unauthorized - You must be the owner or a player',
                'required_owner_id' => $gameSession->game->user_id,
                'your_user_id' => auth()->id()
            ], 403);
        }

        // Get players ordered by score
        $players = $gameSession->players()
            ->with('user')
            ->orderByDesc('score')
            ->get();

        return response()->json([
            'success' => true,
            'leaderboard' => GameSessionPlayerResource::collection($players)
        ]);

    } catch (\Exception $e) {
        \Log::error('Failed to get leaderboard', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'session_id' => $id,
            'user_id' => auth()->id()
        ]);
        return response()->json([
            'message' => 'Failed to retrieve leaderboard',
            'error' => $e->getMessage()
        ], 500);
    }
}
}