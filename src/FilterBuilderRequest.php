<?php

namespace Mykolab\FilterBuilder;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Mykolab\FilterBuilder\Enums\SortDirection;

class FilterBuilderRequest extends Request
{
    protected ?Collection $requestData = null;

    public static function fromRequest(Request $request): FilterBuilderRequest
    {
        return static::createFrom($request);
    }

    /**
     * @return Collection<string, mixed>
     */
    public function filters(): Collection
    {
        $ignoreParameters = [
            config('filter-builder.request_parameters.order_by'),
            config('filter-builder.request_parameters.order_direction'),
        ];

        return $this->getRequestData()->except($ignoreParameters);
    }

    public function sortBy(): ?string
    {
        $sortParameterName = config('filter-builder.request_parameters.order_by');

        return $this->getRequestData()->get($sortParameterName);
    }

    public function sortDirection(): SortDirection
    {
        $sortDirParameterName = config('filter-builder.request_parameters.order_direction');

        return SortDirection::tryFrom($this->getRequestData()->get($sortDirParameterName)) ?? SortDirection::ASCENDING;
    }

    private function getRequestData(): Collection
    {
        if (! $this->requestData) {
            $this->requestData = (new RequestValuesTransformer($this->all()))();
        }

        return $this->requestData;
    }
}

