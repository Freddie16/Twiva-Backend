<?php

namespace App\Http\Requests\Api\Game;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreGameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'sometimes|in:draft,published,archived',
            'questions' => 'sometimes|array',
            'questions.*.question_text' => 'required_with:questions|string|max:500',
            'questions.*.points' => 'required_with:questions|integer|min:1|max:100',
            'questions.*.answers' => 'required_with:questions.*|array|min:2',
            'questions.*.answers.*.answer_text' => 'required|string|max:255',
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
}