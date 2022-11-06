<?php

namespace Mykolab\FilterBuilder\Search;

use Illuminate\Database\Query\Expression;

class Searchable
{
    public bool $wildCardAtStart = true;

    public bool $wildCardAtEnd = true;

    public bool $caseInsensitive = true;

    public function __construct(public readonly Expression|string $property)
    {
    }

    public static function make(Expression|string $property): static
    {
        return new static($property);
    }

    public function disableWildCardAtStart(): static
    {
        $this->wildCardAtStart = false;

        return $this;
    }

    public function disableWildCardAtEnd(): static
    {
        $this->wildCardAtEnd = false;

        return $this;
    }

    public function disableCaseInsensitive(): static
    {
        $this->caseInsensitive = false;

        return $this;
    }
}
