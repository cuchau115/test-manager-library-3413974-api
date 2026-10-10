<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'document_number' => $this->document_number,
            'active_loans' => $this->when(isset($this->active_loans), $this->active_loans),
        ];
    }
}