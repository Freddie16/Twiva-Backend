<?php

namespace App\Http\Requests\Api\GameSession;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\GameSession; // Import GameSession model

class SubmitAnswerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
         // Check if the authenticated user is a player in this session
        $gameSession = $this->route('gameSession');
        if (!$gameSession) {
            return false;
        }

        return $gameSession->players()->where('user_id', auth()->id())->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'question_id' => 'required|exists:questions,id',
            'answer_id' => 'nullable|exists:answers,id', // Allow null if skipping
        ];
    }
}