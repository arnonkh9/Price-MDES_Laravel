<?php

namespace Database\Factories;

use App\Models\Product;
use App\Support\GeneratesUUID;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    use GeneratesUUID;

    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'id' => self::generateID('p'),
            'category' => fake()->randomElement(['Notebook', 'All-in-One', 'Desktop PC', 'Server']),
            'brand' => fake()->randomElement(['ASUS', 'HP', 'Dell', 'Lenovo']),
            'model' => fake()->word() . ' ' . fake()->numerify('###'),
            'price' => fake()->numberBetween(10000, 100000),
            'price_unit' => 'บาท/เครื่อง',
            'price_date' => '2569-05-21',
            'price_source' => fake()->randomElement(['Excel', 'กรอกด้วยมือ', 'ดาวน์โหลดจากเว็บ']),
            'price_url' => null,
            'specs' => [
                'Processor' => fake()->word(),
            ],
        ];
    }
}
