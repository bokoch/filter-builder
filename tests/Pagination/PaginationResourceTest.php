<?php

use Illuminate\Http\Request;
use Mykolab\FilterBuilder\Pagination\PaginationData;
use Mykolab\FilterBuilder\Pagination\PaginationResource;

it('has proper pagination meta data', function () {
    $paginationData = new PaginationData(1, 10, 25);
    $paginationResource = new PaginationResource(['foo' => 'bar'], $paginationData);

    expect($paginationResource->toArray(new Request()))->toMatchArray(
        [
            'data' => ['foo' => 'bar'],
            'current_page' => $paginationData->currentPage,
            'per_page' => $paginationData->perPage,
            'total_pages' => $paginationData->getTotalPages(),
            'total_items' => $paginationData->totalItems,
        ]
    );
});
