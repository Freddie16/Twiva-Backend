<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Question;
use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\QuestionResource;

class QuestionController extends Controller
{
    public function store(Request $request, $game_id)
    {
        DB::beginTransaction();
        
        try {
            $game = Game::find((int)$game_id);
            
            if (!$game) {
                return response()->json([
                    'success' => false,
                    'message' => 'Game not found'
                ], 404);
            }
        
            // Verify ownership
            if ($game->user_id != auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to add questions to this game'
                ], 403);
            }

            // Validate input
            $validator = Validator::make($request->all(), [
                'question_text' => 'required|string|max:500',
                'points' => 'required|integer|min:1|max:100',
                'answers' => 'required|array|min:2',
                'answers.*.answer_text' => 'required|string|max:255',
                'answers.*.is_correct' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Create question
            $question = $game->questions()->create([
                'question_text' => $request->question_text,
                'points' => $request->points
            ]);

            // Create answers
            $question->answers()->createMany($request->answers);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => new QuestionResource($question->load('answers'))
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Question creation failed: '.$e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Server error',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function update(Request $request, $question_id)
{
    DB::beginTransaction();
    
    try {
        $question = Question::find($question_id);
        
        if (!$question) {
            return response()->json([
                'success' => false,
                'message' => 'Question not found'
            ], 404);
        }
            // Validate input
            $validator = Validator::make($request->all(), [
                'question_text' => 'sometimes|string|max:500',
                'points' => 'sometimes|integer|min:1|max:100',
                'answers' => 'sometimes|array|min:2',
                'answers.*.answer_text' => 'required_with:answers|string|max:255',
                'answers.*.is_correct' => 'required_with:answers|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update question
            $question->update($request->only(['question_text', 'points']));

            // Update answers if provided
            if ($request->has('answers')) {
                $question->answers()->delete();
                $question->answers()->createMany($request->answers);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => new QuestionResource($question->fresh('answers'))
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Question update failed: '.$e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Server error',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function destroy(Question $question)
    {
        try {
            // Verify ownership
            if ($question->game->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this question'
                ], 403);
            }

            $question->delete();

            return response()->json([
                'success' => true,
                'message' => 'Question deleted successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Question deletion failed: '.$e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Server error',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }
}