<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

class PaginatedResource extends JsonResource
{
    public function __construct(
        private LengthAwarePaginator $paginator,
        private string $resourceClass,
    ) {
        parent::__construct($paginator);
    }

    public function toArray($request): array
    {
        $paginator = $this->resource;
        $items = $paginator->getCollection()->map(fn ($item) => new $this->resourceClass($item))->all();

        return [
            'items' => $items,
            'page' => $paginator->currentPage(),
            'size' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];
    }
}