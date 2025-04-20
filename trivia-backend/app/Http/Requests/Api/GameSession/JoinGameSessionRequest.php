<?php

namespace App\Http\Requests\Api\GameSession;

use Illuminate\Foundation\Http\FormRequest;

class JoinGameSessionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check(); // Only authenticated users can join
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
{
    return [
        'session_code' => 'required|string|exists:game_sessions,code', // Changed to check 'code' column
    ];
}
}