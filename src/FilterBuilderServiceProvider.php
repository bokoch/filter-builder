<?php

namespace Mykolab\FilterBuilder;

use Illuminate\Foundation\Application;
use Mykolab\FilterBuilder\Pagination\Resolvers\PaginationResolver;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilterBuilderServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-filter-builder')
            ->hasConfigFile();
    }

    public function registeringPackage()
    {
        $this->app->bind(FilterBuilderRequest::class, function ($app) {
            return FilterBuilderRequest::fromRequest($app['request']);
        });

        $this->app->bind(
            PaginationResolver::class,
            fn (Application $app) => $app->make($app['config']['filter-builder']['default_pagination_resolver'])
        );
    }
}
