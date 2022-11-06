<?php

namespace Mykolab\FilterBuilder\Sorts;

use Illuminate\Database\Eloquent\Builder;
use Mykolab\FilterBuilder\Enums\SortDirection;

class FieldSort implements Sort
{
    public function __invoke(Builder $query, string $property, SortDirection $sortDirection)
    {
        $query->orderBy($property, $sortDirection->value);
    }
}
