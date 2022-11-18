<?php

use Illuminate\Support\Facades\DB;
use Mykolab\FilterBuilder\AllowedFilters\ExactAllowedFilter;
use Mykolab\FilterBuilder\Tests\TestClasses\Models\RelatedModel;
use Mykolab\FilterBuilder\Tests\TestClasses\Models\TestModel;

it('can filter by exact value of filter field', function () {
    $models = TestModel::factory(5)->create();

    $actualResults = createFilterBuilderFromRequest(
        [
            'name' => $models->first()->name,
        ])
        ->allowedFilters([
            ExactAllowedFilter::make('name'),
        ])
        ->get();

    expect($actualResults)->toHaveCount(1);

    $actualResults = createFilterBuilderFromRequest(
        [
            'name' => $models->first()->name.'foo',
        ])
        ->allowedFilters([
            ExactAllowedFilter::make('name'),
        ])
        ->get();

    expect($actualResults)->toBeEmpty();
});

it('can filter by allowed filter name alias', function () {
    $models = TestModel::factory(5)->create();

    $actualResults = createFilterBuilderFromRequest(
        [
            'foo' => $models->first()->name,
        ])
        ->allowedFilters([
            ExactAllowedFilter::make('foo', 'name'),
        ])
        ->get();

    expect($actualResults)->toHaveCount(1);
});

it('will not perform any filtering if it is not allowed', function () {
    $models = TestModel::factory(5)->create();

    $actualResults = createFilterBuilderFromRequest(
        [
            'name' => $models->first()->name,
        ])
        ->get();

    expect($actualResults)->toHaveCount(5);
});

it('will not perform any filtering if filter value is empty', function () {
    TestModel::factory(5)->create();

    $actualResults = createFilterBuilderFromRequest(
        [
            'name' => '',
        ])
        ->allowedFilters([
            ExactAllowedFilter::make('name'),
        ])
        ->get();

    expect($actualResults)->toHaveCount(5);
});

it('can use raw db expression as internal name', function () {
    $models = TestModel::factory(5)->create();

    $actualResults = createFilterBuilderFromRequest(
        [
            'name' => $models->first()->name,
        ])
        ->allowedFilters([
            ExactAllowedFilter::make('name', DB::raw('name')),
        ])
        ->get();

    expect($actualResults)->toHaveCount(1);
});

it('can filter by "having" statement', function () {
    RelatedModel::factory(3)
        ->has(TestModel::factory(3)->state(['price' => 100]))
        ->create();

    RelatedModel::factory()
        ->has(TestModel::factory(3)->state(['price' => 200]))
        ->create();
    DB::enableQueryLog();

    $query = RelatedModel::query()
        ->selectRaw('
            related_models.*,
            sum(t.price) as total_price
        ')
        ->join('test_models as t', 't.related_model_id', '=', 'related_models.id')
        ->groupBy('related_models.id');

    $baseSql = $query->toSql();

    $actualResults = createFilterBuilderFromRequest(
        ['total_price' => 600],
        $query
    )
        ->allowedFilters([
            ExactAllowedFilter::make('total_price', DB::raw('sum(t.price)'), true),
        ])
        ->get();

    assertQueryExecuted($baseSql.' having sum(t.price) = ?');

    expect($actualResults)->toHaveCount(1);
});
