<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LoanItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'book_id' => $this->book_id,
            'book_title' => $this->book_title,
            'isbn' => $this->isbn,
            'category_name' => $this->category_name,
            'due_at' => $this->due_at?->format('Y-m-d\TH:i:s\Z'),
            'returned_at' => $this->returned_at?->format('Y-m-d\TH:i:s\Z'),
        ];
    }
}