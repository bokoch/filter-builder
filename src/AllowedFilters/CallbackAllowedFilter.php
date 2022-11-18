<?php

namespace Mykolab\FilterBuilder\AllowedFilters;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Mykolab\FilterBuilder\Filters\CallbackFilter;

class CallbackAllowedFilter extends BaseAllowedFilter
{
    /**
     * @param  string  $name
     * @param Closure(Builder $query, Expression|string $property, mixed $value): void $closure
     * @return static
     */
    public static function make(string $name, Closure $closure): static
    {
        return new static($name, new CallbackFilter($closure));
    }
}
