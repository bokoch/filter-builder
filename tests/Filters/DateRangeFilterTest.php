<?php

use Illuminate\Support\Facades\DB;
use Mykolab\FilterBuilder\AllowedFilters\DateRangeAllowedFilter;
use Mykolab\FilterBuilder\Enums\DateUnit;
use Mykolab\FilterBuilder\Tests\TestClasses\Models\RelatedModel;
use Mykolab\FilterBuilder\Tests\TestClasses\Models\TestModel;

it('can filter by date range, which include edges.', function () {
    TestModel::factory(2)->create(['published_at' => now()]);
    TestModel::factory(3)->create(['published_at' => now()->addDays(3)]);

    DB::enableQueryLog();

    $actualResults = createFilterBuilderFromRequest(
        [
            'published_at_from' => now()->addDay()->format('Y-m-d H:i'),
            'published_at_to' => now()->addDays(4)->format('Y-m-d H:i'),
        ])
        ->allowedFilters([
            DateRangeAllowedFilter::make('published_at')
        ])
        ->get();

    assertQueryExecuted('select * from "test_models" where "published_at" >= ? and "published_at" <= ?');

    expect($actualResults)->toHaveCount(3);
});

it('can filter by date range, with missing "end" edge', function () {
    TestModel::factory(2)->create(['published_at' => now()]);
    TestModel::factory(3)->create(['published_at' => now()->addDays(3)]);

    DB::enableQueryLog();

    $actualResults = createFilterBuilderFromRequest(
        [
            'published_at_from' => now()->addDay()->format('Y-m-d H:i'),
        ])
        ->allowedFilters([
            DateRangeAllowedFilter::make('published_at')
        ])
        ->get();

    assertQueryExecuted('select * from "test_models" where "published_at" >= ?');

    expect($actualResults)->toHaveCount(3);
});

it('can filter by date range, with missing "start" edge', function () {
    TestModel::factory(2)->create(['published_at' => now()]);
    TestModel::factory(3)->create(['published_at' => now()->addDays(3)]);

    DB::enableQueryLog();

    $actualResults = createFilterBuilderFromRequest(
        [
            'published_at_to' => now()->addDays(2)->format('Y-m-d H:i'),
        ])
        ->allowedFilters([
            DateRangeAllowedFilter::make('published_at')
        ])
        ->get();

    assertQueryExecuted('select * from "test_models" where "published_at" <= ?');

    expect($actualResults)->toHaveCount(2);
});

it('will not perform any filtering if both edges are empty', function () {
    TestModel::factory(2)->create(['published_at' => now()]);
    TestModel::factory(3)->create(['published_at' => now()->addDays(3)]);

    $actualResults = createFilterBuilderFromRequest(
        [
            'published_at_from' => '',
            'published_at_to' => '',
        ])
        ->allowedFilters([
            DateRangeAllowedFilter::make('published_at')
        ])
        ->get();

    expect($actualResults)->toHaveCount(5);
});

it('can use raw db expression as internal name', function () {
    TestModel::factory(2)->create(['published_at' => now()]);
    TestModel::factory(3)->create(['published_at' => now()->addDays(3)]);

    $actualResults = createFilterBuilderFromRequest(
        [
            'published_at_from' => now()->addDay()->format('Y-m-d H:i'),
            'published_at_to' => now()->addDays(4)->format('Y-m-d H:i'),
        ])
        ->allowedFilters([
            DateRangeAllowedFilter::make('published_at', DB::raw('published_at'))
        ])
        ->get();

    expect($actualResults)->toHaveCount(3);
});

it('can filter by "having" statement', function () {
    RelatedModel::factory(3)
        ->has(TestModel::factory(3)->state(['published_at' => now()]))
        ->create();

    RelatedModel::factory()
        ->has(TestModel::factory(3)->state(['published_at' => now()->addDays(3)]))
        ->create();
    DB::enableQueryLog();

    $query = RelatedModel::query()
        ->selectRaw('
            related_models.*,
            min(t.published_at) as min_published_at
        ')
        ->join('test_models as t', 't.related_model_id', '=', 'related_models.id')
        ->groupBy('related_models.id');

    $baseSql = $query->toSql();

    $actualResults = createFilterBuilderFromRequest(
        [
            'published_at_from' => now()->addDay()->format('Y-m-d H:i'),
            'published_at_to' => now()->addDays(4)->format('Y-m-d H:i'),
        ],
        $query
    )
        ->allowedFilters([
            DateRangeAllowedFilter::make('published_at', DB::raw('min(t.published_at)'), true)
        ])
        ->get();

    assertQueryExecuted($baseSql . ' having min(t.published_at) >= ? and min(t.published_at) <= ?');

    expect($actualResults)->toHaveCount(1);
});

it('will round date to day unit by default', function () {
    TestModel::factory(2)->create(['published_at' => now()]);
    TestModel::factory(3)->create(['published_at' => now()->addDays(3)]);

    $actualResults = createFilterBuilderFromRequest(
        [
            'published_at_from' => now()->format('Y-m-d H:i'),
            'published_at_to' => now()->format('Y-m-d H:i'),
        ])
        ->allowedFilters([
            DateRangeAllowedFilter::make('published_at')->roundDatesTo(),
        ])
        ->get();

    expect($actualResults)->toHaveCount(2);
});

it('can round date to date unit', function () {
    TestModel::factory(2)->create(['published_at' => now()]);
    TestModel::factory(3)->create(['published_at' => now()->addDays(3)]);
    TestModel::factory(3)->create(['published_at' => now()->addMonths(3)]);

    $actualResults = createFilterBuilderFromRequest(
        [
            'published_at_from' => now()->format('Y-m-d H:i'),
            'published_at_to' => now()->format('Y-m-d H:i'),
        ])
        ->allowedFilters([
            DateRangeAllowedFilter::make('published_at')->roundDatesTo(DateUnit::MONTH),
        ])
        ->get();

    expect($actualResults)->toHaveCount(5);
});
