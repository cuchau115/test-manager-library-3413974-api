<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'from' => $this->from,
            'to' => $this->to,
            'rows' => $this->rows,
        ];
    }
}