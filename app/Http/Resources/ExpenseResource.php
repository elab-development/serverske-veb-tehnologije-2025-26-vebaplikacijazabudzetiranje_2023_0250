<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
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
        'description' => $this->description,
        'category' => $this->category,
        'amount' => $this->amount,
        'group' => $this->group->name ?? null,
        'paid_by' => $this->payer->name ?? null,
        'created_at' => $this->created_at->format('d.m.Y H:i'),
    ];
}
}