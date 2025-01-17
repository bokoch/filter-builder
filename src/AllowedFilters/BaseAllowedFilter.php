<?php

namespace Mykolab\FilterBuilder\AllowedFilters;

use Illuminate\Database\Query\Expression;
use Mykolab\FilterBuilder\FilterBuilder;
use Mykolab\FilterBuilder\Filters\Filter;

abstract class BaseAllowedFilter implements AllowedFilter
{
    public function __construct(
        protected readonly string $name,
        protected readonly Filter $filter,
        protected Expression|string|null $internalName = null
    ) {
        $this->internalName ??= $this->name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function filter(FilterBuilder $filterBuilder, $value): void
    {
        ($this->filter)($filterBuilder, $this->internalName, $this->prepareValue($value));
    }

    protected function prepareValue($value): mixed
    {
        return $value;
    }
}
