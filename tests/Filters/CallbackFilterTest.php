<?php

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
use Mykolab\FilterBuilder\AllowedFilters\CallbackAllowedFilter;
use Mykolab\FilterBuilder\Tests\TestClasses\Models\TestModel;

it('can filter by callback', function () {
    $models = TestModel::factory(5)->create(['published_at' => now()]);

    DB::enableQueryLog();

    $actualResults = createFilterBuilderFromRequest(
        [
            'status' => 'pending',
        ])
        ->allowedFilters([
            CallbackAllowedFilter::make('status', function (Builder $query, Expression|string $property, mixed $value) {
                expect($property)->toBe('status');

                $query->where($property, $value)->whereNotNull('published_at');
            }),
        ])
        ->get();

    assertQueryExecuted('select * from "test_models" where "status" = ? and "published_at" is not null');

    expect($actualResults)->toHaveCount($models->where('status', 'pending')->count());
});
