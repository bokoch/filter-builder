<?php

use Illuminate\Support\Facades\DB;
use Mykolab\FilterBuilder\AllowedFilters\RangeAllowedFilter;
use Mykolab\FilterBuilder\Tests\TestClasses\Models\RelatedModel;
use Mykolab\FilterBuilder\Tests\TestClasses\Models\TestModel;

it('can filter by range, which include edges.', function () {
    TestModel::factory(2)->create(['price' => 300]);
    TestModel::factory(3)->create(['price' => 500]);

    DB::enableQueryLog();

    $actualResults = createFilterBuilderFromRequest(
        [
            'price_from' => 400,
            'price_to' => 500,
        ])
        ->allowedFilters([
            RangeAllowedFilter::make('price'),
        ])
        ->get();

    assertQueryExecuted('select * from "test_models" where "price" >= ? and "price" <= ?');

    expect($actualResults)->toHaveCount(3);
});

it('can filter by range, with missing "end" edge', function () {
    TestModel::factory(2)->create(['price' => 300]);
    TestModel::factory(3)->create(['price' => 500]);

    DB::enableQueryLog();

    $actualResults = createFilterBuilderFromRequest(
        [
            'price_from' => 400,
        ])
        ->allowedFilters([
            RangeAllowedFilter::make('price'),
        ])
        ->get();

    assertQueryExecuted('select * from "test_models" where "price" >= ?');

    expect($actualResults)->toHaveCount(3);
});

it('can filter by range, with missing "start" edge', function () {
    TestModel::factory(2)->create(['price' => 300]);
    TestModel::factory(3)->create(['price' => 500]);

    DB::enableQueryLog();

    $actualResults = createFilterBuilderFromRequest(
        [
            'price_to' => 400,
        ])
        ->allowedFilters([
            RangeAllowedFilter::make('price'),
        ])
        ->get();

    assertQueryExecuted('select * from "test_models" where "price" <= ?');

    expect($actualResults)->toHaveCount(2);
});

it('will not perform any filtering if both edges are empty', function () {
    TestModel::factory(2)->create(['price' => 300]);
    TestModel::factory(3)->create(['price' => 500]);

    $actualResults = createFilterBuilderFromRequest(
        [
            'price_from' => '',
            'price_to' => '',
        ])
        ->allowedFilters([
            RangeAllowedFilter::make('price'),
        ])
        ->get();

    expect($actualResults)->toHaveCount(5);
});

it('can use raw db expression as internal name', function () {
    TestModel::factory(2)->create(['price' => 300]);
    TestModel::factory(3)->create(['price' => 500]);

    $actualResults = createFilterBuilderFromRequest(
        [
            'price_from' => 400,
            'price_to' => 500,
        ])
        ->allowedFilters([
            RangeAllowedFilter::make('price', DB::raw('price')),
        ])
        ->get();

    expect($actualResults)->toHaveCount(3);
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
        [
            'total_price_from' => 500,
            'total_price_to' => 700,
        ],
        $query
    )
        ->allowedFilters([
            RangeAllowedFilter::make('total_price', DB::raw('sum(t.price)'), true),
        ])
        ->get();

    assertQueryExecuted($baseSql.' having sum(t.price) >= ? and sum(t.price) <= ?');

    expect($actualResults)->toHaveCount(1);
});

it('will perform filtering with POST request array', function () {
    TestModel::factory(2)->create(['price' => 300]);
    TestModel::factory(3)->create(['price' => 500]);

    $actualResults = createFilterBuilderFromRequest(
        [
            'price' => [
                'from' => 200,
                'to' => 400,
            ],
        ])
        ->allowedFilters([
            RangeAllowedFilter::make('price'),
        ])
        ->get();

    expect($actualResults)->toHaveCount(2);
});
