<?php

namespace App\Http\Requests\Api\GameSession;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\GameSession; // Import GameSession model

class SubmitAnswerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
{
    $gameSession = $this->route('gameSession'); // Make sure this matches your route parameter
    
    if (!$gameSession) {
        return false;
    }

    return $gameSession->players()->where('user_id', auth()->id())->exists();
}

public function rules()
{
    return [
        'question_id' => 'required|exists:questions,id',
        'answer_id' => 'nullable|exists:answers,id'
    ];
}
}