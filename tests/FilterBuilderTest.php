<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Mockery\MockInterface;
use Mykolab\FilterBuilder\AllowedFilters\ExactAllowedFilter;
use Mykolab\FilterBuilder\FilterBuilder;
use Mykolab\FilterBuilder\FilterBuilderRequest;
use Mykolab\FilterBuilder\Pagination\Resolvers\PaginationResolver;
use Mykolab\FilterBuilder\Tests\TestClasses\Models\TestModel;
use Mykolab\FilterBuilder\Tests\TestClasses\Pagination\TestPaginationResource;
use Mykolab\FilterBuilder\Tests\TestClasses\TestModelResource;

it('will not perform any filtering if value is empty', function () {
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

it('will convert string value of allowed filter to exact filter', function () {
    $models = TestModel::factory(5)->create();

    $actualResults = createFilterBuilderFromRequest(
        [
            'name' => $models->first()->name,
        ])
        ->allowedFilters([
            'name',
        ])
        ->get();

    expect($actualResults)->toHaveCount(1);

    $actualResults = createFilterBuilderFromRequest(
        [
            'name' => $models->first()->name.'foo',
        ])
        ->allowedFilters([
            'name',
        ])
        ->get();

    expect($actualResults)->toBeEmpty();
});

it('will convert string value of allowed sorting to field sort', function () {
    TestModel::factory(5)->create();
    DB::enableQueryLog();

    $actualResults = createFilterBuilderFromRequest(
        [
            'order_by' => 'id',
        ])
        ->allowedSorts([
            'id',
        ])
        ->get();

    assertQueryExecuted('select * from "test_models" order by "id" asc');

    assertSortedAscending($actualResults, 'id');
});

it('will not perform sorting if it has not allowed field', function () {
    TestModel::factory(5)->create();
    DB::enableQueryLog();

    $actualResults = createFilterBuilderFromRequest(
        [
            'order_by' => 'name',
        ])
        ->allowedSorts([
            'id',
        ])
        ->get();

    assertQueryExecuted('select * from "test_models"');
});

it('will not perform sorting if it has empty value', function () {
    TestModel::factory(5)->create();
    DB::enableQueryLog();

    $actualResults = createFilterBuilderFromRequest(
        [
            'order_by' => '',
        ])
        ->allowedSorts([
            'id',
        ])
        ->get();

    assertQueryExecuted('select * from "test_models"');
});

it('can resolve query from model class', function () {
    $filterBuilder = FilterBuilder::for(TestModel::class);

    expect($filterBuilder->getQueryBuilder()->toSql())->toBe('select * from "test_models"');
});

it('can paginate results', function () {
    $this->instance(
        PaginationResolver::class,
        Mockery::mock(
            PaginationResolver::class,
            function (MockInterface $mock) {
                $mock->expects('makePaginationResource');
            }
        )
    );

    FilterBuilder::for(TestModel::class)->paginate();
});

it('can paginate results with custom pagination resolver', function () {
    Route::get('/test-model', function () {
        $this->instance(
            PaginationResolver::class,
            Mockery::mock(
                PaginationResolver::class,
                function (MockInterface $mock) {
                    $mock
                        ->expects('makePaginationResource')
                        ->andReturn(
                            TestPaginationResource::make(TestModel::factory()->make())
                        );
                }
            )
        );

        return FilterBuilder::for(TestModel::class)->paginate();
    });

    $this->getJson('/test-model')->assertJson([
        'data' => [
            'foo' => 'bar',
        ],
    ]);
});

it('can paginate results with custom resource', function () {
    $this->instance(
        PaginationResolver::class,
        Mockery::mock(
            PaginationResolver::class,
            function (MockInterface $mock) {
                $mock
                    ->shouldReceive('makePaginationResource')
                    ->withSomeOfArgs(TestModelResource::class)
                    ->once();
            }
        )
    );

    FilterBuilder::for(TestModel::class)->resource(TestModelResource::class)->paginate();
});

it('will resolve the request when its not given', function () {
    $builderReflection = new ReflectionClass(FilterBuilder::class);
    $requestProperty = $builderReflection->getProperty('request');
    $requestProperty->setAccessible(true);

    $this->getJson('/test-model?order_by=name');

    $builder = FilterBuilder::for(TestModel::class);

    expect($requestProperty->getValue($builder))->toBeInstanceOf(FilterBuilderRequest::class);
    expect($requestProperty->getValue($builder)->sortBy())->toEqual('name');
});

it('can filter data and paginate response from get request', function () {
    TestModel::factory(20)->create(['status' => 'pending']);

    DB::enableQueryLog();

    Route::get('test-models', function () {
        return FilterBuilder::for(TestModel::class)
            ->allowedSorts(['id'])
            ->allowedFilters(['status'])
            ->resource(TestModelResource::class)
            ->paginate();
    });

    $this->getJson('/test-models?status=pending&per_page=5&order_by=id&order_direction=desc')
        ->assertJsonCount(5, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'status',
                ],
            ],
            'current_page',
            'per_page',
            'total_pages',
            'total_items',
        ]);

    assertQueryExecuted('select * from "test_models" where "status" = ? order by "id" desc limit 5 offset 0');
});

it('can filter data and paginate response from post request', function () {
    TestModel::factory(20)->create(['status' => 'pending']);

    DB::enableQueryLog();

    Route::post('test-models', function () {
        return FilterBuilder::for(TestModel::class)
            ->allowedSorts(['id'])
            ->allowedFilters(['status'])
            ->resource(TestModelResource::class)
            ->paginate();
    });

    $this->postJson('/test-models', [
        'status' => 'pending',
        'per_page' => 5,
        'order_by' => 'id',
        'order_direction' => 'desc',
    ])
        ->assertJsonCount(5, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'status',
                ],
            ],
            'current_page',
            'per_page',
            'total_pages',
            'total_items',
        ]);

    assertQueryExecuted('select * from "test_models" where "status" = ? order by "id" desc limit 5 offset 0');
});
