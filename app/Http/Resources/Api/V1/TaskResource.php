<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return  [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'due_date' => $this->due_date?->format('d-m-Y'),
            'created' => [
                'human' => $this->created_at?->diffForHumans(),
                'string' => $this->created_at?->toIso8601String(),
            ],
            'assigned_to' => $this->whenLoaded(
                'assignedTo',
                fn () => $this->assignedTo === null ? null : [
                    'id' => $this->assignedTo->id,
                    'name' => $this->assignedTo->name,
                ]
            ),
        ];
    }
}
