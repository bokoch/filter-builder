<?php

namespace Mykolab\FilterBuilder\Pagination\Resolvers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\JsonResource;
use Mykolab\FilterBuilder\FilterBuilderRequest;
use Mykolab\FilterBuilder\Pagination\PaginationData;
use Mykolab\FilterBuilder\Pagination\PaginationResource;

class DefaultPaginationResolver implements PaginationResolver
{
    /**
     * {@inheritdoc}
     */
    public function makePaginationResource(
        Builder $query,
        FilterBuilderRequest $request,
        ?string $resourceClass = null
    ): JsonResource {
        $page = (int) $request->get(config('filter-builder.request_parameters.current_page'));
        $page = $page > 0 ? $page : 1;

        $perPage = $request->get(
            config('filter-builder.request_parameters.per_page'),
            config('filter-builder.per_page_default')
        );

        $paginationData = new PaginationData($page, $perPage, $query->count());

        $results = $query
            ->offset($paginationData->getOffset())
            ->limit($paginationData->perPage)
            ->get();

        $resource = $resourceClass ? $resourceClass::collection($results) : $results;

        return new PaginationResource($resource, $paginationData);
    }
}
