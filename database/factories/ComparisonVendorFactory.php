<?php

namespace Database\Factories;

use App\Models\ComparisonVendor;
use Illuminate\Database\Eloquent\Factories\Factory;

class ComparisonVendorFactory extends Factory
{
    protected $model = ComparisonVendor::class;

    public function definition(): array
    {
        return [
            'position' => fake()->numberBetween(1, 3),
            'name' => fake()->company(),
            'brand' => fake()->randomElement(['ASUS', 'HP', 'Dell', 'Lenovo']),
            'model' => fake()->bothify('Model-####'),
            'price' => fake()->numberBetween(15000, 60000),
            'specs' => ['Processor' => 'Intel Core i5'],
        ];
    }
}
