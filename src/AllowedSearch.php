<?php

namespace Mykolab\FilterBuilder;

use Closure;
use Mykolab\FilterBuilder\Search\CallbackSearch;
use Mykolab\FilterBuilder\Search\FieldSearch;
use Mykolab\FilterBuilder\Search\Search;
use Mykolab\FilterBuilder\Search\Searchable;

class AllowedSearch
{
    public function __construct(
        protected readonly Search $search,
        public readonly string $searchParameterName
    ) {
    }

    public function search(FilterBuilder $filterBuilder, string $value): void
    {
        ($this->search)($filterBuilder->getQueryBuilder(), $value);
    }

    /**
     * @param  array<Searchable|string>  $searchable
     * @param  string|null  $searchParameterName
     * @return static
     */
    public static function searchable(array $searchable, ?string $searchParameterName = null): static
    {
        $searchable = collect($searchable)->map(function (Searchable|string $searchable) {
            if ($searchable instanceof Searchable) {
                return $searchable;
            }

            return Searchable::make($searchable);
        });

        $searchParameterName ??= config('filter-builder.request_parameters.search');

        return new static(new FieldSearch($searchable), $searchParameterName);
    }

    public static function callback(Closure $closure, ?string $searchParameterName = null): static
    {
        $searchParameterName ??= config('filter-builder.request_parameters.search');

        return new static(new CallbackSearch($closure), $searchParameterName);
    }
}
