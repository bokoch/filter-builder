<?php

namespace Mykolab\FilterBuilder;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Mykolab\FilterBuilder\AllowedFilters\AllowedFilter;
use Mykolab\FilterBuilder\AllowedFilters\ExactAllowedFilter;
use Mykolab\FilterBuilder\Pagination\Resolvers\PaginationResolver;

class FilterBuilder
{
    protected ?AllowedSearch $allowedSearch = null;

    /** @var Collection<AllowedFilter> */
    protected Collection $allowedFilters;

    /** @var Collection<AllowedSort> */
    protected Collection $allowedSorts;

    protected FilterBuilderRequest $request;

    /** @var class-string<JsonResource>|null */
    protected ?string $resourceClass = null;

    public function __construct(
        public readonly Builder $query,
        ?Request $request = null
    ) {
        $this->initializeRequest($request);
    }

    private function initializeRequest(?Request $request): void
    {
        $this->request = FilterBuilderRequest::fromRequest($request ?? app(Request::class));
    }

    /**
     * @param  Builder|class-string<Model>  $subject
     * @return $this
     */
    public static function for(Builder|string $subject): static
    {
        if (is_subclass_of($subject, Model::class)) {
            $subject = $subject::query();
        }

        return new static($subject);
    }

    public function getQueryBuilder(): Builder
    {
        return $this->query;
    }

    public function get(): EloquentCollection
    {
        return $this->query->get();
    }

    public function paginate(): JsonResource
    {
        return app(PaginationResolver::class)
            ->makePaginationResource(clone $this->query, $this->request, $this->resourceClass);
    }

    public function allowedSearch(AllowedSearch $allowedSearch): static
    {
        $this->allowedSearch = $allowedSearch;

        if ($this->request->get($allowedSearch->searchParameterName)) {
            $this->allowedSearch->search($this, $this->request->get($allowedSearch->searchParameterName));
        }

        return $this;
    }

    /**
     * @param  class-string<JsonResource>  $resourceClass
     * @return $this
     */
    public function resource(string $resourceClass): static
    {
        $this->resourceClass = $resourceClass;

        return $this;
    }

    public function allowedFilters(array $allowedFilters): static
    {
        $this->allowedFilters = collect($allowedFilters)->map(function (AllowedFilter|string $allowedFilter) {
            if ($allowedFilter instanceof AllowedFilter) {
                return $allowedFilter;
            }

            return ExactAllowedFilter::make($allowedFilter);
        });

        $this->addFiltersToQuery();

        return $this;
    }

    protected function addFiltersToQuery(): void
    {
        $this->allowedFilters->each(function (AllowedFilter $allowedFilter) {
            if ($this->isFilterRequested($allowedFilter)) {
                $value = $this->request->filters()->get($allowedFilter->name);
                if (! empty($value)) {
                    $allowedFilter->filter($this, $value);
                }
            }
        });
    }

    public function isFilterRequested(AllowedFilter $allowedFilter): bool
    {
        return $this->request->filters()->has($allowedFilter->name);
    }

    public function allowedSorts(array $sorts): static
    {
        $this->allowedSorts = collect($sorts)->map(function (string|AllowedSort $sort) {
            if ($sort instanceof AllowedSort) {
                return $sort;
            }

            return AllowedSort::field($sort);
        });

        $this->findSort($this->request->sortBy())?->sort($this, $this->request->sortDirection());

        return $this;
    }

    protected function findSort(?string $property): ?AllowedSort
    {
        if (! $property) {
            return null;
        }

        return $this->allowedSorts
            ->first(function (AllowedSort $sort) use ($property) {
                return $sort->isSort($property);
            });
    }
}
