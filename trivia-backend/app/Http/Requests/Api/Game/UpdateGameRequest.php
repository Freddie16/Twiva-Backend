<?php

namespace App\Http\Requests\Api\Game;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateGameRequest extends FormRequest
{
    public function authorize(): bool
{
    $game = $this->route('game');
    return $game && $game->user_id === auth()->id();
}

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'sometimes|in:draft,published,archived',
            'questions' => 'sometimes|array',
            'questions.*.id' => 'sometimes|exists:questions,id',
            'questions.*.question_text' => 'sometimes|string|max:500',
            'questions.*.points' => 'sometimes|integer|min:1|max:100',
            'questions.*.answers' => 'sometimes|array|min:2',
            'questions.*.answers.*.id' => 'sometimes|exists:answers,id',
            'questions.*.answers.*.answer_text' => 'sometimes|string|max:255',
            'questions.*.answers.*.is_correct' => 'sometimes|boolean',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422)
        );
    }

    protected function prepareForValidation()
    {
        if ($this->has('questions')) {
            $this->merge([
                'questions' => array_values($this->questions)
            ]);
        }
    }
}