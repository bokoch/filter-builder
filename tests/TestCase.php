<?php

namespace Mykolab\FilterBuilder\Tests;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Mykolab\FilterBuilder\FilterBuilderServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Mykolab\\FilterBuilder\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    private function setUpDatabase(Application $app): void
    {
        $app['db']->connection()->getSchemaBuilder()->create('test_models', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('status');
            $table->integer('price');
            $table->boolean('is_visible')->default(true);
            $table->dateTime('published_at')->nullable();
            $table->integer('related_model_id');
            $table->timestamps();
        });

        $app['db']->connection()->getSchemaBuilder()->create('related_models', function (Blueprint $table) {
            $table->increments('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->timestamps();
        });
    }

    protected function getPackageProviders($app)
    {
        return [
            FilterBuilderServiceProvider::class,
        ];
    }

    protected function assertQueryLogContains(string $partialSql)
    {
        $queryLog = collect(DB::getQueryLog())->pluck('query')->implode('|');

        $this->assertStringContainsString($partialSql, $queryLog);
    }

    protected function assertQueryLogDoesntContain(string $partialSql)
    {
        $queryLog = collect(DB::getQueryLog())->pluck('query')->implode('|');

        $this->assertStringNotContainsString($partialSql, $queryLog, 'Query log contained partial SQL: ' . $partialSql);
    }
}
