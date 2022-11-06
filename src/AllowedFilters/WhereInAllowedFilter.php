<?php

namespace Mykolab\FilterBuilder\AllowedFilters;

use Illuminate\Database\Query\Expression;
use Mykolab\FilterBuilder\Filters\WhereInFilter;

class WhereInAllowedFilter extends AllowedFilter
{
    private array $allowedOptions = [];

    private string $delimiter = ',';

    public static function make(string $name, Expression|string|null $internalName = null): static
    {
        return new static($name, new WhereInFilter(), $internalName);
    }

    protected function prepareValue($value): mixed
    {
        $preparedValue = $value;

        if (! is_array($value)) {
            $preparedValue = explode($this->delimiter, $value);
        }

        if ($this->allowedOptions) {
            $preparedValue = array_intersect($preparedValue, $this->allowedOptions);
        }

        return $preparedValue;
    }

    public function allowedOptions(array $allowedOptions): static
    {
        $this->allowedOptions = $allowedOptions;

        return $this;
    }

    public function delimiter(string $delimiter): static
    {
        $this->delimiter = $delimiter;

        return $this;
    }
}
