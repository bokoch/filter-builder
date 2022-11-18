<?php

namespace Mykolab\FilterBuilder\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Mykolab\FilterBuilder\Tests\TestClasses\Models\RelatedModel;

class RelatedModelFactory extends Factory
{
    protected $model = RelatedModel::class;

    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
        ];
    }
}
