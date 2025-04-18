<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\GameResource;
use App\Http\Resources\GameSessionPlayerResource;

class GameSessionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'game' => new GameResource($this->whenLoaded('game')), // Include game details
            'session_code' => $this->session_code,
            'status' => $this->status,
            'players' => GameSessionPlayerResource::collection($this->whenLoaded('players')), // Include players
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}