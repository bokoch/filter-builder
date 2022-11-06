<?php

namespace Mykolab\FilterBuilder;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class RequestValuesTransformer
{
    public function __construct(private readonly array $requestData)
    {
    }

    public function __invoke(): Collection
    {
        $fromSuffix = config('filter-builder.request_parameters.range_suffix.from');
        $toSuffix = config('filter-builder.request_parameters.range_suffix.to');

        $transformedData = [];
        foreach ($this->requestData as $name => $value) {
            $transformedValue = $this->transformValue($value);

            if (Str::endsWith($name, $fromSuffix)) {
                Arr::set($transformedData, Str::beforeLast($name, $fromSuffix).'.from', $transformedValue);

                continue;
            }

            if (Str::endsWith($name, $toSuffix)) {
                Arr::set($transformedData, Str::beforeLast($name, $toSuffix).'.to', $transformedValue);

                continue;
            }

            Arr::set($transformedData, $name, $transformedValue);
        }

        return collect($transformedData);
    }

    private function transformValue($value): mixed
    {
        if ($value === 'true') {
            return true;
        }

        if ($value === 'false') {
            return false;
        }

        return $value;
    }
}
