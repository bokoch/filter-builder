<?php

namespace Mykolab\FilterBuilder\Sorts;

use Illuminate\Database\Eloquent\Builder;
use Mykolab\FilterBuilder\Enums\SortDirection;

interface Sort
{
    public function __invoke(Builder $query, string $property, SortDirection $sortDirection);
}
