<?php

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Mykolab\FilterBuilder\AllowedSort;
use Mykolab\FilterBuilder\Enums\SortDirection;
use Mykolab\FilterBuilder\Tests\TestClasses\Models\TestModel;

beforeEach(function () {
    DB::enableQueryLog();

    TestModel::factory()->count(5)->create();
});

it('can sort a query by defined property ascending', function () {
    $actualResults = createFilterBuilderFromRequest(['order_by' => 'id'])
        ->allowedSorts([
            AllowedSort::field('id'),
        ])
        ->get();

    assertQueryExecuted('select * from "test_models" order by "id" asc');

    assertSortedAscending($actualResults, 'id');
});

it('can sort a query by defined property descending', function () {
    $actualResults = createFilterBuilderFromRequest(
        [
            'order_by' => 'id',
            'order_direction' => 'desc',
        ])
        ->allowedSorts([
            AllowedSort::field('id'),
        ])
        ->get();

    assertQueryExecuted('select * from "test_models" order by "id" desc');

    assertSortedDescending($actualResults, 'id');
});

it('will not sort a query if sort field is not allowed', function () {
    $actualResults = createFilterBuilderFromRequest(
        [
            'order_by' => 'id',
            'order_direction' => 'desc',
        ])
        ->get();

    assertQueryExecuted('select * from "test_models"');

    assertSortedAscending($actualResults, 'id');
});

it('can sort by alias field', function () {
    $actualResults = createFilterBuilderFromRequest(
        [
            'order_by' => 'foo',
            'order_direction' => 'desc',
        ])
        ->allowedSorts([
            AllowedSort::field('foo', 'id'),
        ])
        ->get();

    assertQueryExecuted('select * from "test_models" order by "id" desc');

    assertSortedDescending($actualResults, 'id');
});

it('can sort a query by callback', function () {
    $actualResults = createFilterBuilderFromRequest(
        [
            'order_by' => 'id',
            'order_direction' => 'desc',
        ])
        ->allowedSorts([
            AllowedSort::callback('id', function (Builder $query, string $property, SortDirection $sortDirection) {
                $query->orderBy($property, $sortDirection->value)->orderBy('name', $sortDirection->value);
            }),
        ])
        ->get();

    assertQueryExecuted('select * from "test_models" order by "id" desc, "name" desc');

    $expectedResults = TestModel::query()
        ->orderBy('id', 'desc')
        ->orderBy('name', 'desc')
        ->get();

    expect($actualResults->pluck('id')->toArray())->toMatchArray($expectedResults->pluck('id')->toArray());
});
