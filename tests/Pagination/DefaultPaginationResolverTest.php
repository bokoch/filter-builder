<?php

use Illuminate\Http\Request;
use Mykolab\FilterBuilder\FilterBuilderRequest;
use Mykolab\FilterBuilder\Pagination\Resolvers\DefaultPaginationResolver;
use Mykolab\FilterBuilder\Tests\TestClasses\Models\TestModel;
use Mykolab\FilterBuilder\Tests\TestClasses\TestModelResource;

it('has default per_page and page values', function () {
    TestModel::factory(20)->create();

    $fbRequest = FilterBuilderRequest::fromRequest(
        $request = new Request([])
    );

    $paginationResolver = new DefaultPaginationResolver();
    $resource = $paginationResolver->makePaginationResource(
        TestModel::query(),
        $fbRequest,
        TestModelResource::class
    );

    $result = $resource->toArray($request);
    expect($result['per_page'])->toBe(10);
    expect($result['page'])->toBe(1);
});

it('resolves per_page and page values from request', function () {
    TestModel::factory(20)->create();

    $fbRequest = FilterBuilderRequest::fromRequest(
        $request = new Request([
            'per_page' => 15,
            'page' => 2,
        ])
    );

    $paginationResolver = new DefaultPaginationResolver();
    $resource = $paginationResolver->makePaginationResource(
        TestModel::query(),
        $fbRequest,
        TestModelResource::class
    );

    $result = $resource->toArray($request);
    expect($result['per_page'])->toBe(15);
    expect($result['page'])->toBe(2);
    expect($result['total_items'])->toBe(20);
});

it('will not have page less than 1', function () {
    TestModel::factory(20)->create();

    $fbRequest = FilterBuilderRequest::fromRequest(
        $request = new Request([
            'page' => 0,
        ])
    );

    $paginationResolver = new DefaultPaginationResolver();
    $resource = $paginationResolver->makePaginationResource(
        TestModel::query(),
        $fbRequest,
        TestModelResource::class
    );

    $result = $resource->toArray($request);
    expect($result['page'])->toBe(1);

    TestModel::factory(20)->create();

    $fbRequest = FilterBuilderRequest::fromRequest(
        $request = new Request([
            'page' => -12,
        ])
    );

    $paginationResolver = new DefaultPaginationResolver();
    $resource = $paginationResolver->makePaginationResource(
        TestModel::query(),
        $fbRequest,
        TestModelResource::class
    );

    $result = $resource->toArray($request);
    expect($result['page'])->toBe(1);
});
