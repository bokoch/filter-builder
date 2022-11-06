<?php

namespace Mykolab\FilterBuilder\AllowedFilters;

use Illuminate\Database\Query\Expression;
use Mykolab\FilterBuilder\Filters\ExactFilter;

class ExactAllowedFilter extends AllowedFilter
{
    public static function make(
        string $name,
        Expression|string|null $internalName = null,
        bool $useHaving = false
    ): static {
        return new static($name, new ExactFilter($useHaving), $internalName);
    }
}
