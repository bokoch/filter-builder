<?php

namespace Mykolab\FilterBuilder\Pagination;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaginationResource extends JsonResource
{
    public function __construct($resource, private readonly PaginationData $paginationData)
    {
        parent::__construct($resource);
    }

    /**
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'data' => $this->resource,
            'page' => $this->paginationData->currentPage,
            'per_page' => $this->paginationData->perPage,
            'total_pages' => $this->paginationData->getTotalPages(),
            'total_items' => $this->paginationData->totalItems,
        ];
    }
}
