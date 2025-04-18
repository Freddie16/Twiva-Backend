<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\QuestionResource;

class GameResource extends JsonResource
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
            'creator' => new UserResource($this->whenLoaded('creator')), // Include creator when loaded
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'questions' => QuestionResource::collection($this->whenLoaded('questions')), // Include questions when loaded
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}