<?php

use Illuminate\Support\Facades\DB;
use Mykolab\FilterBuilder\AllowedFilters\LikeAllowedFilter;
use Mykolab\FilterBuilder\Tests\TestClasses\Models\RelatedModel;
use Mykolab\FilterBuilder\Tests\TestClasses\Models\TestModel;

it('can filter by exact value of filter field', function () {
    $models = TestModel::factory(5)->create();

    $actualResults = createFilterBuilderFromRequest(
        [
            'name' => $models->first()->name,
        ])
        ->allowedFilters([
            LikeAllowedFilter::make('name'),
        ])
        ->get();

    expect($actualResults)->toHaveCount(1);
});

it('can filter by start with value by default', function () {
    TestModel::factory(5)->create();
    TestModel::factory()->create(['name' => 'foo bar']);

    $actualResults = createFilterBuilderFromRequest(
        [
            'name' => 'foo',
        ])
        ->allowedFilters([
            LikeAllowedFilter::make('name'),
        ])
        ->get();

    expect($actualResults)->toHaveCount(1);
});

it('can disable filtering by start with value', function () {
    TestModel::factory(5)->create();
    TestModel::factory()->create(['name' => 'foo bar']);

    $actualResults = createFilterBuilderFromRequest(
        [
            'name' => 'foo',
        ])
        ->allowedFilters([
            LikeAllowedFilter::make('name')->wildCardAtEnd(false),
        ])
        ->get();

    expect($actualResults)->toBeEmpty();
});

it('has disabled filter by end with value by default', function () {
    TestModel::factory(5)->create();
    TestModel::factory()->create(['name' => 'foo bar']);

    $actualResults = createFilterBuilderFromRequest(
        [
            'name' => 'bar',
        ])
        ->allowedFilters([
            LikeAllowedFilter::make('name'),
        ])
        ->get();

    expect($actualResults)->toBeEmpty();
});

it('can be filtered by end with value', function () {
    TestModel::factory(5)->create();
    TestModel::factory()->create(['name' => 'foo bar']);

    $actualResults = createFilterBuilderFromRequest(
        [
            'name' => 'bar',
        ])
        ->allowedFilters([
            LikeAllowedFilter::make('name')->wildCardAtStart(),
        ])
        ->get();

    expect($actualResults)->toHaveCount(1);
});

it('has case sensitive by default', function () {
    TestModel::factory(5)->create();
    TestModel::factory()->create(['name' => 'foo bar']);

    $actualResults = createFilterBuilderFromRequest(
        [
            'name' => 'FOO',
        ])
        ->allowedFilters([
            LikeAllowedFilter::make('name'),
        ])
        ->get();

    expect($actualResults)->toBeEmpty();
});

it('can be case insensitive', function () {
    TestModel::factory(5)->create();
    TestModel::factory()->create(['name' => 'foo bar']);

    $actualResults = createFilterBuilderFromRequest(
        [
            'name' => 'FOO',
        ])
        ->allowedFilters([
            LikeAllowedFilter::make('name', caseInsensitive: true),
        ])
        ->get();

    expect($actualResults)->toHaveCount(1);
});

it('can use raw db expression as internal name', function () {
    TestModel::factory(5)->create();
    TestModel::factory()->create(['name' => 'foo bar']);

    $actualResults = createFilterBuilderFromRequest(
        [
            'name' => 'foo',
        ])
        ->allowedFilters([
            LikeAllowedFilter::make('name', DB::raw('name')),
        ])
        ->get();

    expect($actualResults)->toHaveCount(1);
});

it('can filter by "having" statement', function () {
    RelatedModel::factory(3)
        ->has(TestModel::factory(3))
        ->create();

    $expected = RelatedModel::factory()
        ->has(TestModel::factory(3))
        ->create();
    DB::enableQueryLog();

    $query = RelatedModel::query()
        ->selectRaw("
            related_models.*,
            string_agg(t.name, ',') as names
        ")
        ->join('test_models as t', 't.related_model_id', '=', 'related_models.id')
        ->groupBy('related_models.id');

    $baseSql = $query->toSql();

    $expectedNames = $expected->testModels->take(2)->implode('name', ',');

    $actualResults = createFilterBuilderFromRequest(
        ['names' => $expectedNames],
        $query
    )
        ->allowedFilters([
            LikeAllowedFilter::make('names', DB::raw("string_agg(t.name, ',')"), useHaving: true),
        ])
        ->get();

    assertQueryExecuted($baseSql." having string_agg(t.name, ',') like ?");

    expect($actualResults)->toHaveCount(1);
});
