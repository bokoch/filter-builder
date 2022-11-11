<?php

use Mykolab\FilterBuilder\Pagination\PaginationData;

it('can calculate offset', function () {
    $paginationData = new PaginationData(1, 10, 25);
    expect($paginationData->getOffset())->toBe(0);

    $paginationData = new PaginationData(2, 10, 25);
    expect($paginationData->getOffset())->toBe(10);
});

it('can calculate total pages', function () {
    $paginationData = new PaginationData(1, 10, 25);
    expect($paginationData->getTotalPages())->toBe(3);

    $paginationData = new PaginationData(2, 15, 25);
    expect($paginationData->getTotalPages())->toBe(2);
});
