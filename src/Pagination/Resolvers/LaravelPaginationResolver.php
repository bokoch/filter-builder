<?php

namespace Mykolab\FilterBuilder\Pagination\Resolvers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\JsonResource;
use Mykolab\FilterBuilder\FilterBuilderRequest;

class LaravelPaginationResolver implements PaginationResolver
{
    /**
     * {@inheritdoc}
     */
    public function makePaginationResource(
        Builder $query,
        FilterBuilderRequest $request,
        ?string $resourceClass = null
    ): JsonResource {
        $results = $query->paginate();

        return $resourceClass ? $resourceClass::collection($results) : $results;
    }
}
