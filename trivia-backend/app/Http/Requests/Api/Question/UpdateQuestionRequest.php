<?php

namespace App\Http\Requests\Api\Question;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Question; // Import the Question model

class UpdateQuestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
         // Only the game creator can update their questions
        $question = $this->route('question'); // Get the question from the route parameters
        return $question && $question->game->user_id === auth()->id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'question_text' => 'sometimes|required|string',
            'points' => 'sometimes|nullable|integer|min:1',
            'answers' => 'sometimes|required|array|min:2', // Allow updating answers
            'answers.*.answer_text' => 'required|string',
            'answers.*.is_correct' => 'required|boolean',
        ];
    }
}