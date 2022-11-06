<?php

namespace Mykolab\FilterBuilder\Pagination\Resolvers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Mykolab\FilterBuilder\FilterBuilderRequest;

interface PaginationResolver
{
    /**
     * @param  Builder  $query
     * @param  FilterBuilderRequest  $request
     * @param  class-string<Model>|null  $resourceClass
     * @return JsonResource
     */
    public function makePaginationResource(
        Builder $query,
        FilterBuilderRequest $request,
        ?string $resourceClass = null
    ): JsonResource;
}
