<?php

namespace Database\Factories;

use App\Models\CharacteristicsTemplate;
use App\Support\GeneratesUUID;
use Illuminate\Database\Eloquent\Factories\Factory;

class CharacteristicsTemplateFactory extends Factory
{
    use GeneratesUUID;

    protected $model = CharacteristicsTemplate::class;

    public function definition(): array
    {
        return [
            'id' => self::generateID('sp'),
            'name' => fake()->sentence(),
            'category' => fake()->randomElement(['Notebook', 'All-in-One', 'Desktop PC', 'Server']),
            'budget' => fake()->numberBetween(20000, 100000),
            'year' => '2569',
            'month' => '05',
            'purpose' => fake()->sentence(),
            'created_date' => '2569-05-21',
            'created_by' => 'admin',
            'specs' => [
                'Processor' => fake()->word(),
            ],
        ];
    }
}
