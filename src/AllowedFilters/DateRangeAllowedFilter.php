<?php

namespace Mykolab\FilterBuilder\AllowedFilters;

use Carbon\Carbon;
use Illuminate\Database\Query\Expression;
use Mykolab\FilterBuilder\Enums\DateUnit;
use Mykolab\FilterBuilder\Filters\RangeFilter;

class DateRangeAllowedFilter extends AllowedFilter
{
    private ?DateUnit $dateUnit = null;

    public static function make(
        string $name,
        Expression|string|null $internalName = null,
        bool $useHaving = false
    ): static {
        return new static($name, new RangeFilter($useHaving), $internalName);
    }

    protected function prepareValue($value): mixed
    {
        $preparedValue['from'] = $this->getFromDate($value['from'] ?? null);
        $preparedValue['to'] = $this->getToDate($value['to'] ?? null);

        return $preparedValue;
    }

    public function roundDatesTo(DateUnit $dateUnit = DateUnit::DAY): static
    {
        $this->dateUnit = $dateUnit;

        return $this;
    }

    private function getFromDate(?string $date): ?Carbon
    {
        if (! $date) {
            return null;
        }

        $from = Carbon::parse($date);
        if ($this->dateUnit) {
            $from = $from->startOf($this->dateUnit->value);
        }

        return $from;
    }

    private function getToDate(?string $date): ?Carbon
    {
        if (! $date) {
            return null;
        }

        $to = Carbon::parse($date);
        if ($this->dateUnit) {
            $to = $to->endOf($this->dateUnit->value);
        }

        return $to;
    }
}
