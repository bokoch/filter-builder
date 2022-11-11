<?php

namespace Mykolab\FilterBuilder\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Mykolab\FilterBuilder\Tests\TestClasses\Models\RelatedModel;
use Mykolab\FilterBuilder\Tests\TestClasses\Models\TestModel;

class TestModelFactory extends Factory
{
    protected $model = TestModel::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'status' => $this->faker->randomElement(['pending', 'awaiting', 'completed']),
            'price' => $this->faker->numberBetween(100, 999),
            'is_visible' => $this->faker->boolean,
            'related_model_id' => RelatedModel::factory(),
        ];
    }
}
