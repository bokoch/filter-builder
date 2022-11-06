<?php

namespace Mykolab\FilterBuilder\Filters;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Mykolab\FilterBuilder\FilterBuilder;

class CallbackFilter implements Filter
{
    /**
     * @param Closure(Builder $query, Expression|string $propery, mixed $value): void $closure
     */
    public function __construct(
        private readonly Closure $closure
    ) {
    }

    public function __invoke(FilterBuilder $filterBuilder, Expression|string $property, mixed $value): void
    {
        call_user_func($this->closure, $filterBuilder->getQueryBuilder(), $property, $value);
    }
}
