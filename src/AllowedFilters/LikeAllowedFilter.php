<?php

namespace Mykolab\FilterBuilder\AllowedFilters;

use Illuminate\Database\Query\Expression;
use Illuminate\Support\Stringable;
use Mykolab\FilterBuilder\Filters\LikeFilter;

class LikeAllowedFilter extends BaseAllowedFilter
{
    private bool $wildCardAtStart = false;

    private bool $wildCardAtEnd = true;

    public static function make(
        string $name,
        Expression|string|null $internalName = null,
        bool $caseInsensitive = false,
        bool $useHaving = false
    ): static {
        return new static($name, new LikeFilter($caseInsensitive, $useHaving), $internalName);
    }

    protected function prepareValue($value): mixed
    {
        return (string) str($value)
            ->when($this->wildCardAtStart, fn (Stringable $str) => $str->prepend('%'))
            ->when($this->wildCardAtEnd, fn (Stringable $str) => $str->append('%'));
    }

    public function wildCardAtStart(bool $enabled = true): static
    {
        $this->wildCardAtStart = $enabled;

        return $this;
    }

    public function wildCardAtEnd(bool $enabled = true): static
    {
        $this->wildCardAtEnd = $enabled;

        return $this;
    }
}
