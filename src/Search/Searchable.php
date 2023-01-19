<?php

namespace Mykolab\FilterBuilder\Search;

use Illuminate\Database\Query\Expression;

class Searchable
{
    public bool $wildCardAtStart = false;

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
        return $this->wildCardAtStart(false);
    }

    public function wildCardAtStart(bool $enabled = true): static
    {
        $this->wildCardAtStart = $enabled;

        return $this;
    }

    public function disableWildCardAtEnd(): static
    {
        return $this->wildCardAtEnd(false);
    }

    public function wildCardAtEnd(bool $enabled = true): static
    {
        $this->wildCardAtEnd = $enabled;

        return $this;
    }

    public function disableCaseInsensitive(): static
    {
        $this->caseInsensitive = false;

        return $this;
    }
}
