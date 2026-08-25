<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupResource extends JsonResource
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
    'name' => $this->name,
    'created_by' => $this->creator->name ?? null,
    'members_count' => $this->members->count(),
    'created_at' => $this->created_at->format('d.m.Y H:i'),
];
    }
}
