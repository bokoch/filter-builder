<?php

use Mykolab\FilterBuilder\FilterBuilderRequest;
use Mykolab\FilterBuilder\Pagination\Resolvers\DefaultPaginationResolver;
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

        $this->app->bind(PaginationResolver::class, DefaultPaginationResolver::class);
    }
}
