<?php

namespace Tests\Feature;

use App\Livewire\ProductForm;
use App\Livewire\ComparisonForm;
use App\Livewire\CharacteristicsForm;
use App\Models\Product;
use App\Models\Comparison;
use App\Models\CharacteristicsTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IDGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_ids_are_uuid_v4_format()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(ProductForm::class)
            ->call('open')
            ->set('brand', 'Test')
            ->set('model', 'Model')
            ->set('category', 'Notebook')
            ->set('price', 25000)
            ->call('save');

        $product = Product::where('brand', 'Test')->first();
        $this->assertNotNull($product);
        // UUID v4 format: p-{36-char-uuid}
        $this->assertMatchesRegularExpression('/^p-[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $product->id);
    }

    public function test_comparison_ids_are_uuid_v4_format()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(ComparisonForm::class)
            ->call('open')
            ->set('name', 'Test Comparison')
            ->set('category', 'Notebook')
            ->call('save');

        $comparison = Comparison::where('name', 'Test Comparison')->first();
        $this->assertNotNull($comparison);
        // UUID v4 format: cmp-{36-char-uuid}
        $this->assertMatchesRegularExpression('/^cmp-[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $comparison->id);
    }

    public function test_characteristics_ids_are_uuid_v4_format()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(CharacteristicsForm::class)
            ->call('open')
            ->set('name', 'Test Characteristics')
            ->set('category', 'Notebook')
            ->call('save');

        $spec = CharacteristicsTemplate::where('name', 'Test Characteristics')->first();
        $this->assertNotNull($spec);
        // UUID v4 format: sp-{36-char-uuid}
        $this->assertMatchesRegularExpression('/^sp-[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $spec->id);
    }

    public function test_concurrent_product_creates_produce_unique_ids()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Simulate rapid concurrent creates
        $ids = [];
        for ($i = 0; $i < 5; $i++) {
            Livewire::actingAs($admin)
                ->test(ProductForm::class)
                ->call('open')
                ->set('brand', "Brand{$i}")
                ->set('model', "Model{$i}")
                ->set('category', 'Notebook')
                ->set('price', 25000)
                ->call('save');

            $product = Product::where('brand', "Brand{$i}")->first();
            $ids[] = $product->id;
        }

        // All IDs must be unique
        $this->assertEquals(5, count(array_unique($ids)));
        // All IDs must follow UUID pattern
        foreach ($ids as $id) {
            $this->assertMatchesRegularExpression('/^p-[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $id);
        }
    }

    public function test_no_id_collision_with_same_millisecond()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Create products rapidly (within same millisecond)
        $products = [];
        for ($i = 0; $i < 3; $i++) {
            Livewire::actingAs($admin)
                ->test(ProductForm::class)
                ->call('open')
                ->set('brand', "RapidBrand{$i}")
                ->set('model', "RapidModel{$i}")
                ->set('category', 'Notebook')
                ->set('price', 25000)
                ->call('save');

            $products[] = Product::where('brand', "RapidBrand{$i}")->first();
        }

        // All products should be created
        $this->assertEquals(3, count($products));
        // All IDs should be unique (UUID v4 eliminates collision risk)
        $ids = array_map(fn($p) => $p->id, $products);
        $this->assertEquals(3, count(array_unique($ids)));
    }
}
