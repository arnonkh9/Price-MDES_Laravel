<?php

namespace Database\Factories;

use App\Models\Comparison;
use App\Support\GeneratesUUID;
use Illuminate\Database\Eloquent\Factories\Factory;

class ComparisonFactory extends Factory
{
    use GeneratesUUID;

    protected $model = Comparison::class;

    public function definition(): array
    {
        return [
            'id' => self::generateID('cmp'),
            'name' => fake()->sentence(),
            'category' => fake()->randomElement(['Notebook', 'All-in-One', 'Desktop PC', 'Server']),
            'year' => '2569',
            'month' => '05',
            'characteristics_template_id' => null,
            'notes' => fake()->sentence(),
            'status' => 'draft',
            'created_date' => '2569-05-21',
            'created_by' => 'admin',
        ];
    }
}
