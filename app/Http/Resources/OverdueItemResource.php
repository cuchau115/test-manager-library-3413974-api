<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OverdueItemResource extends JsonResource
{
    public function toArray($request): array
    {
        $dueAt = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $this->due_at);
        $now = new \DateTimeImmutable();
        $daysLate = (int) $now->diff($dueAt)->days;

        return [
            'loan_id' => $this->loan_id,
            'item_id' => $this->id,
            'book_title' => $this->book_title,
            'member_name' => $this->loan?->member?->full_name ?? '',
            'due_at' => $dueAt?->format('Y-m-d\TH:i:s\Z'),
            'days_late' => $daysLate,
        ];
    }
}