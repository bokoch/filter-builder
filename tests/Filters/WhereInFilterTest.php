<?php

use Illuminate\Support\Facades\DB;
use Mykolab\FilterBuilder\AllowedFilters\WhereInAllowedFilter;
use Mykolab\FilterBuilder\Tests\TestClasses\Models\TestModel;

it('can filter by in array values', function () {
    $models = TestModel::factory(5)->create();

    $actualResults = createFilterBuilderFromRequest(
        [
            'status' => 'pending',
        ])
        ->allowedFilters([
            WhereInAllowedFilter::make('status'),
        ])
        ->get();

    expect($actualResults)->toHaveCount($models->where('status', 'pending')->count());
});

it('can filter by allowed filter name alias', function () {
    $models = TestModel::factory(5)->create();

    $actualResults = createFilterBuilderFromRequest(
        [
            'foo' => 'pending',
        ])
        ->allowedFilters([
            WhereInAllowedFilter::make('foo', 'status'),
        ])
        ->get();

    expect($actualResults)->toHaveCount($models->where('status', 'pending')->count());
});

it('can use raw db expression as internal name', function () {
    $models = TestModel::factory(5)->create();

    $actualResults = createFilterBuilderFromRequest(
        [
            'status' => 'pending',
        ])
        ->allowedFilters([
            WhereInAllowedFilter::make('status', DB::raw('status')),
        ])
        ->get();

    expect($actualResults)->toHaveCount($models->where('status', 'pending')->count());
});

it('can filter by multiple values from array', function () {
    TestModel::factory(2)->create(['status' => 'pending']);
    TestModel::factory(3)->create(['status' => 'awaiting']);
    TestModel::factory(3)->create(['status' => 'completed']);

    $actualResults = createFilterBuilderFromRequest(
        [
            'status' => ['completed', 'pending'],
        ])
        ->allowedFilters([
            WhereInAllowedFilter::make('status'),
        ])
        ->get();

    expect($actualResults)->toHaveCount(5);
});

it('can filter by multiple values parsed from string with comma default delimiter', function () {
    TestModel::factory(2)->create(['status' => 'pending']);
    TestModel::factory(3)->create(['status' => 'awaiting']);
    TestModel::factory(3)->create(['status' => 'completed']);

    $actualResults = createFilterBuilderFromRequest(
        [
            'status' => 'completed,pending',
        ])
        ->allowedFilters([
            WhereInAllowedFilter::make('status'),
        ])
        ->get();

    expect($actualResults)->toHaveCount(5);
});

it('can filter by multiple values parsed from string with custom delimiter', function () {
    TestModel::factory(2)->create(['status' => 'pending']);
    TestModel::factory(3)->create(['status' => 'awaiting']);
    TestModel::factory(3)->create(['status' => 'completed']);

    $actualResults = createFilterBuilderFromRequest(
        [
            'status' => 'completed|pending',
        ])
        ->allowedFilters([
            WhereInAllowedFilter::make('status')->delimiter('|'),
        ])
        ->get();

    expect($actualResults)->toHaveCount(5);
});

it('can restrict filter options', function () {
    TestModel::factory(2)->create(['status' => 'pending']);
    TestModel::factory(3)->create(['status' => 'awaiting']);

    $actualResults = createFilterBuilderFromRequest(
        [
            'status' => 'pending',
        ])
        ->allowedFilters([
            WhereInAllowedFilter::make('status')->allowedOptions(['awaiting']),
        ])
        ->get();

    expect($actualResults)->toBeEmpty();

    $actualResults = createFilterBuilderFromRequest(
        [
            'status' => 'pending',
        ])
        ->allowedFilters([
            WhereInAllowedFilter::make('status')->allowedOptions(['pending']),
        ])
        ->get();

    expect($actualResults)->toHaveCount(2);
});
