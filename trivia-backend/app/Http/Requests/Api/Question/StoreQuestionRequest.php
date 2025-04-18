<?php

namespace App\Http\Requests\Api\Question;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Game; // Import the Game model

class StoreQuestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only the game creator can add questions to the game
        $game = $this->route('game'); // Get the game from the route parameters
         return $game && $game->user_id === auth()->id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'question_text' => 'required|string',
            'points' => 'nullable|integer|min:1',
            'answers' => 'required|array|min:2', // At least two answer options
            'answers.*.answer_text' => 'required|string',
            'answers.*.is_correct' => 'required|boolean',
        ];
    }
}