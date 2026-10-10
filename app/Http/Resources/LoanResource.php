<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LoanResource extends JsonResource
{
    public function toArray($request): array
    {
        $now = new \DateTimeImmutable();
        return [
            'id' => $this->id,
            'member_id' => $this->member_id,
            'loaned_at' => $this->loaned_at?->format('Y-m-d\TH:i:s\Z'),
            'status' => $this->status($now),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($i) => new LoanItemResource($i))->all()),
            'member_name' => $this->whenLoaded('member', fn () => $this->member->full_name),
        ];
    }
}