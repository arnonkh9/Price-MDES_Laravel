<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('specs.categories') as $i => $cat) {
            Category::updateOrCreate(
                ['slug' => $cat['id']],
                [
                    'label' => $cat['label'],
                    'short' => $cat['short'],
                    'color' => $cat['color'],
                    'position' => $i,
                ]
            );
        }
    }
}
