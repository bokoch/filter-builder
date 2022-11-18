<?php

namespace Mykolab\FilterBuilder\AllowedFilters;

use Mykolab\FilterBuilder\FilterBuilder;

interface AllowedFilter
{
    public function getName(): string;

    public function filter(FilterBuilder $filterBuilder, $value): void;
}
