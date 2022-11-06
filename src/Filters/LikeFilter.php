<?php

namespace Mykolab\FilterBuilder\Filters;

use Illuminate\Database\Query\Expression;
use Mykolab\FilterBuilder\FilterBuilder;

class LikeFilter implements Filter
{
    public function __construct(
        private readonly bool $caseInsensitive = false,
        private readonly bool $useHaving = false
    ) {
    }

    public function __invoke(FilterBuilder $filterBuilder, Expression|string $property, mixed $value): void
    {
        $conditionStatement = $this->useHaving ? 'having' : 'where';
        $likeOperator = $this->caseInsensitive ? 'ilike' : 'like';

        $filterBuilder->getQueryBuilder()->$conditionStatement($property, $likeOperator, $value);
    }
}
