<?php

namespace Mykolab\FilterBuilder\Search;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class CallbackSearch implements Search
{
    /**
     * @param Closure(Builder $query, string $value): void $closure
     */
    public function __construct(private readonly Closure $closure)
    {
    }

    public function __invoke(Builder $query, string $value): void
    {
        $query->where(
            fn (Builder $query) => call_user_func($this->closure, $query, $value)
        );
    }
}
