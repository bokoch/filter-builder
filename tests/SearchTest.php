<?php

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mykolab\FilterBuilder\AllowedSearch;
use Mykolab\FilterBuilder\Search\Searchable;
use Mykolab\FilterBuilder\Tests\TestClasses\Models\TestModel;

beforeEach(function () {
    $this->models = TestModel::factory(5)->create();
});

it('can search by allowed field', function () {
    $actualResults = createFilterBuilderFromRequest([
        'search' => $this->models->first()->name,
    ])
        ->allowedSearch(
            AllowedSearch::searchable([
                Searchable::make('name'),
            ])
        )
        ->get();

    expect($actualResults)->toHaveCount(1);
});

it('can accept string value as searchable column', function () {
    $actualResults = createFilterBuilderFromRequest([
        'search' => $this->models->first()->name,
    ])
        ->allowedSearch(
            AllowedSearch::searchable([
                'name',
            ])
        )
        ->get();

    expect($actualResults)->toHaveCount(1);
});

it('will search as case insensitive by default', function () {
    $actualResults = createFilterBuilderFromRequest([
        'search' => Str::upper($this->models->first()->name),
    ])
        ->allowedSearch(
            AllowedSearch::searchable([
                Searchable::make('name'),
            ])
        )
        ->get();

    expect($actualResults)->toHaveCount(1);
});

it('can search as case sensitive', function () {
    $actualResults = createFilterBuilderFromRequest([
        'search' => Str::upper($this->models->first()->name),
    ])
        ->allowedSearch(
            AllowedSearch::searchable([
                Searchable::make('name')->disableCaseInsensitive(),
            ])
        )
        ->get();

    expect($actualResults)->toBeEmpty();

    $actualResults = createFilterBuilderFromRequest([
        'search' => $this->models->first()->name,
    ])
        ->allowedSearch(
            AllowedSearch::searchable([
                Searchable::make('name')->disableCaseInsensitive(),
            ])
        )
        ->get();

    expect($actualResults)->toHaveCount(1);
});

it('can search part in the middle of column by default', function () {
    TestModel::factory()->create(['name' => 'test foo bar xyz']);

    $actualResults = createFilterBuilderFromRequest([
        'search' => 'foo bar',
    ])
        ->allowedSearch(
            AllowedSearch::searchable([
                Searchable::make('name'),
            ])
        )
        ->get();

    expect($actualResults)->toHaveCount(1);
});

it('can search part with strict start of column', function () {
    TestModel::factory()->create(['name' => 'test foo bar xyz']);

    $actualResults = createFilterBuilderFromRequest([
        'search' => 'foo bar',
    ])
        ->allowedSearch(
            AllowedSearch::searchable([
                Searchable::make('name')->disableWildCardAtStart(),
            ])
        )
        ->get();

    expect($actualResults)->toBeEmpty();

    $actualResults = createFilterBuilderFromRequest([
        'search' => 'test foo bar',
    ])
        ->allowedSearch(
            AllowedSearch::searchable([
                Searchable::make('name')->disableWildCardAtStart(),
            ])
        )
        ->get();

    expect($actualResults)->toHaveCount(1);
});

it('can search part with strict end of column', function () {
    TestModel::factory()->create(['name' => 'test foo bar xyz']);

    $actualResults = createFilterBuilderFromRequest([
        'search' => 'foo bar',
    ])
        ->allowedSearch(
            AllowedSearch::searchable([
                Searchable::make('name')->disableWildCardAtEnd(),
            ])
        )
        ->get();

    expect($actualResults)->toBeEmpty();

    $actualResults = createFilterBuilderFromRequest([
        'search' => 'foo bar xyz',
    ])
        ->allowedSearch(
            AllowedSearch::searchable([
                Searchable::make('name')->disableWildCardAtEnd(),
            ])
        )
        ->get();

    expect($actualResults)->toHaveCount(1);
});

it('will not perform any search if search parameter is empty string', function () {
    $actualResults = createFilterBuilderFromRequest([
        'search' => '',
    ])
        ->allowedSearch(
            AllowedSearch::searchable([
                Searchable::make('name'),
            ])
        )
        ->get();

    expect($actualResults)->toHaveCount(5);
});

it('can search with custom search parameter name', function () {
    $actualResults = createFilterBuilderFromRequest([
        'foo' => $this->models->first()->name,
    ])
        ->allowedSearch(
            AllowedSearch::searchable([
                Searchable::make('name'),
            ], 'foo')
        )
        ->get();

    expect($actualResults)->toHaveCount(1);
});

it('can search by callback and wrap callback query into braces', function () {
    DB::enableQueryLog();

    $actualResults = createFilterBuilderFromRequest([
        'search' => $this->models->first()->name,
    ])
        ->allowedSearch(
            AllowedSearch::callback(function (Builder $query, string $value) {
                $query->where('name', $value)->orWhere('id', 1);
            })
        )
        ->get();

    assertQueryExecuted('select * from "test_models" where ("name" = ? or "id" = ?)');

    expect($actualResults)->toHaveCount(1);
});

it('callback will not perform any search if search parameter is empty string', function () {
    DB::enableQueryLog();

    $actualResults = createFilterBuilderFromRequest([
        'search' => '',
    ])
        ->allowedSearch(
            AllowedSearch::callback(function (Builder $query, string $value) {
                $query->where('name', $value)->orWhere('id', 1);
            })
        )
        ->get();

    expect($actualResults)->toHaveCount(5);
});

it('will not perform any filtering if search is not allowed', function () {
    $actualResults = createFilterBuilderFromRequest([
        'search' => $this->models->first()->name,
    ])->get();

    expect($actualResults)->toHaveCount(5);
});

it('will not perform any filtering if no searchable configurations was passed', function () {
    $actualResults = createFilterBuilderFromRequest([
        'search' => $this->models->first()->name,
    ])
        ->allowedSearch(
            AllowedSearch::searchable([])
        )
        ->get();

    expect($actualResults)->toHaveCount(5);
});
