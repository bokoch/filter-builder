<?php

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Mykolab\FilterBuilder\FilterBuilder;
use Mykolab\FilterBuilder\Tests\TestCase;
use Mykolab\FilterBuilder\Tests\TestClasses\Models\TestModel;

uses(TestCase::class)->in(__DIR__);

function createFilterBuilderFromRequest(
    array $requestParameters,
    Builder|string|null $subject = TestModel::class
): FilterBuilder {
    return FilterBuilder::for($subject, new Request($requestParameters));
}

function assertQueryExecuted(string $query): void
{
    $queries = array_map(function ($queryLogItem) {
        return $queryLogItem['query'];
    }, DB::getQueryLog());

    expect($queries)->toContain($query);
}

function assertSortedAscending(Collection $collection, string $key): void
{
    assertSorted($collection, $key);
}

function assertSortedDescending(Collection $collection, string $key): void
{
    assertSorted($collection, $key, true);
}

function assertSorted(Collection $collection, string $key, bool $descending = false): void
{
    $sortedCollection = $collection->sortBy($key, SORT_REGULAR, $descending);

    expect($collection->pluck('id'))->toEqual($sortedCollection->pluck('id'));
}
