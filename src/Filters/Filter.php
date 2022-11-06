<?php

namespace Mykolab\FilterBuilder\Filters;

use Illuminate\Database\Query\Expression;
use Mykolab\FilterBuilder\FilterBuilder;

interface Filter
{
    public function __invoke(FilterBuilder $filterBuilder, Expression|string $property, mixed $value): void;
}
