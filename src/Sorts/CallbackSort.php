<?php

namespace Mykolab\FilterBuilder\Sorts;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Mykolab\FilterBuilder\Enums\SortDirection;

class CallbackSort implements Sort
{
    /**
     * @param Closure(Builder $query, string $propery, SortDirection $sortDirection): void $closure
     */
    public function __construct(private readonly Closure $closure)
    {
    }

    public function __invoke(Builder $query, string $property, SortDirection $sortDirection)
    {
        call_user_func($this->closure, $query, $property, $sortDirection);
    }
}
