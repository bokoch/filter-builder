<?php

namespace Mykolab\FilterBuilder\Search;

use Illuminate\Database\Eloquent\Builder;

interface Search
{
    public function __invoke(Builder $query, string $value): void;
}
