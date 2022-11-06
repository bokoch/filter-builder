<?php

namespace Mykolab\FilterBuilder\Pagination;

class PaginationData
{
    public function __construct(
        public readonly int $currentPage,
        public readonly int $perPage,
        public readonly int $totalItems,
    ) {
    }

    public function getOffset(): int
    {
        return $this->perPage * ($this->currentPage - 1);
    }

    public function getTotalPages(): int
    {
        return ceil($this->totalItems / $this->perPage);
    }
}
