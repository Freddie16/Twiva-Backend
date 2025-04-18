<?php

namespace App\Http\Requests\Api\Game;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Game; // Import the Game model

class UpdateGameRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only the game creator can update the game
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
            'title' => 'sometimes|required|string|max:255', // sometimes means optional
            'description' => 'nullable|string',
            'status' => 'sometimes|required|in:pending,in_progress,completed',
        ];
    }
}