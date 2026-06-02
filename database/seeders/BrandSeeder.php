<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $fromProducts = DB::table('products')->distinct()->pluck('brand');
        $fromVendors = DB::table('comparison_vendors')->distinct()->pluck('brand');

        $names = $fromProducts->merge($fromVendors)
            ->filter()
            ->unique()
            ->sort()
            ->values();

        foreach ($names as $i => $name) {
            Brand::firstOrCreate(['name' => $name], ['position' => $i + 1]);
        }
    }
}
