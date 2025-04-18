<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Question;
use Illuminate\Http\Request;
use App\Http\Requests\Api\Question\StoreQuestionRequest;
use App\Http\Requests\Api\Question\UpdateQuestionRequest;

class QuestionController extends Controller
{
    public function store(StoreQuestionRequest $request, Game $game)
    {
        // Authorization handled by StoreQuestionRequest

        $question = $game->questions()->create($request->validated());

        // Create answers
        foreach ($request->answers as $answerData) {
            $question->answers()->create($answerData);
        }

        $question->load('answers'); // Load answers after creation
        return response()->json($question, 201); // You might want a Resource for Questions too
    }

    public function update(UpdateQuestionRequest $request, Question $question)
    {
         // Authorization handled by UpdateQuestionRequest

        $question->update($request->validated());

        // Update answers
        if ($request->has('answers')) {
            $question->answers()->delete(); // Simple approach: delete and re-create answers
            foreach ($request->answers as $answerData) {
                $question->answers()->create($answerData);
            }
        }

        $question->load('answers'); // Reload answers after update
        return response()->json($question); // You might want a Resource for Questions too
    }

    public function destroy(Question $question)
    {
         // Ensure only the game creator can delete the question
        if ($question->game->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $question->delete();

        return response()->json(['message' => 'Question deleted successfully']);
    }
}