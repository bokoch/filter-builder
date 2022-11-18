<?php

use Mykolab\FilterBuilder\Enums\SortDirection;
use Mykolab\FilterBuilder\FilterBuilderRequest;

it('can get filters', function () {
    $expected = [
        'foo' => 'bar',
        'baz' => 'xyz',
    ];

    $request = new FilterBuilderRequest($expected);

    expect($request->filters())->toMatchArray($expected);
});

it('skip sort by and sort direction keys in filters array', function () {
    $expected = [
        'foo' => 'bar',
        'baz' => 'xyz',
    ];

    $request = new FilterBuilderRequest([
        'foo' => 'bar',
        'baz' => 'xyz',
        'order_by' => 'foo',
        'order_direction' => 'desc',
    ]);

    expect($request->filters())->toMatchArray($expected);
});

it('transform true/false values into bool type', function () {
    $request = new FilterBuilderRequest([
        'foo' => 'true',
        'bar' => 'false',
    ]);

    expect($request->filters())->toMatchArray([
        'foo' => true,
        'bar' => false,
    ]);
});

it('group range from/to parameters into array', function () {
    $request = new FilterBuilderRequest([
        'price_from' => 10,
        'price_to' => 20,
    ]);

    expect($request->filters())->toMatchArray([
        'price' => [
            'from' => 10,
            'to' => 20,
        ],
    ]);

    $request = new FilterBuilderRequest([
        'price_from' => 10,
    ]);

    expect($request->filters())->toMatchArray([
        'price' => [
            'from' => 10,
        ],
    ]);

    $request = new FilterBuilderRequest([
        'price_to' => 10,
    ]);

    expect($request->filters())->toMatchArray([
        'price' => [
            'to' => 10,
        ],
    ]);
});

it('can get sort by', function () {
    $request = new FilterBuilderRequest([
        'order_by' => 'foo',
    ]);

    expect($request->sortBy())->toEqual('foo');
});

it('can get sort direction', function () {
    $request = new FilterBuilderRequest([
        'order_by' => 'foo',
        'order_direction' => 'desc',
    ]);

    expect($request->sortDirection())->toEqual(SortDirection::DESCENDING);
});

it('will get default asc sort direction if it is not correct', function () {
    $request = new FilterBuilderRequest([
        'order_by' => 'foo',
        'order_direction' => 'bar',
    ]);

    expect($request->sortDirection())->toEqual(SortDirection::ASCENDING);
});
