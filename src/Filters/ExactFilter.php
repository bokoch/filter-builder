<?php

namespace Mykolab\FilterBuilder\Filters;

use Illuminate\Database\Query\Expression;
use Mykolab\FilterBuilder\FilterBuilder;

class ExactFilter implements Filter
{
    public function __construct(private readonly bool $useHaving = false)
    {
    }

    public function __invoke(FilterBuilder $filterBuilder, Expression|string $property, mixed $value): void
    {
        $conditionStatement = $this->useHaving ? 'having' : 'where';

        $filterBuilder->getQueryBuilder()->$conditionStatement($property, $value);
    }
}
