<?php

namespace Mykolab\FilterBuilder;

use Closure;
use Mykolab\FilterBuilder\Enums\SortDirection;
use Mykolab\FilterBuilder\Sorts\CallbackSort;
use Mykolab\FilterBuilder\Sorts\FieldSort;
use Mykolab\FilterBuilder\Sorts\Sort;

class AllowedSort
{
    public function __construct(
        protected readonly string $name,
        protected readonly Sort $sort,
        protected ?string $internalName = null
    ) {
        $this->internalName ??= $this->name;
    }

    public function sort(FilterBuilder $filterBuilder, SortDirection $sortDirection): void
    {
        ($this->sort)($filterBuilder->getQueryBuilder(), $this->internalName, $sortDirection);
    }

    public static function field(string $name, ?string $internalName = null): static
    {
        return new static($name, new FieldSort(), $internalName);
    }

    public static function callback(string $name, Closure $closure, ?string $internalName = null): static
    {
        return new static($name, new CallbackSort($closure), $internalName);
    }

    public function isSort(string $sortName): bool
    {
        return $this->name === $sortName;
    }
}
