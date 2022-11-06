<?php

namespace Mykolab\FilterBuilder\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Mykolab\FilterBuilder\FilterBuilder;

class RangeFilter implements Filter
{
    public function __construct(private readonly bool $useHaving = false)
    {
    }

    public function __invoke(FilterBuilder $filterBuilder, Expression|string $property, mixed $value): void
    {
        $from = $value['from'] ?? null;
        $to = $value['to'] ?? null;

        $conditionStatement = $this->useHaving ? 'having' : 'where';

        $filterBuilder->getQueryBuilder()
            ->when($from, fn (Builder $query) => $query->$conditionStatement($property, '>=', $from))
            ->when($to, fn (Builder $query) => $query->$conditionStatement($property, '<=', $to));
    }
}
