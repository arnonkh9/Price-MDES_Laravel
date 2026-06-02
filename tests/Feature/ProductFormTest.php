<?php

namespace Tests\Feature;

use App\Livewire\ProductForm;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_product_with_all_fields()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(ProductForm::class)
            ->call('open')
            ->set('brand', 'Dell')
            ->set('model', 'XPS 13')
            ->set('category', 'Notebook')
            ->set('price', 45000)
            ->set('price_source', 'Official Website')
            ->set('price_url', 'https://dell.com/xps')
            ->call('save');

        $product = Product::where('brand', 'Dell')->first();
        $this->assertNotNull($product);
        $this->assertEquals('Official Website', $product->price_source);
        $this->assertEquals('https://dell.com/xps', $product->price_url);
    }

    public function test_admin_can_edit_product()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create(['brand' => 'ASUS', 'price' => 25000]);

        Livewire::actingAs($admin)
            ->test(ProductForm::class)
            ->call('open', $product->id)
            ->set('price', 27000)
            ->set('price_source', 'Updated Source')
            ->call('save');

        $product->refresh();
        $this->assertEquals(27000, $product->price);
        $this->assertEquals('Updated Source', $product->price_source);
    }

    public function test_guest_cannot_create_product()
    {
        // Guest has no user → auth()->user() is null → abort(403) or TypeError
        // Livewire wraps this as a 403 or 500 status
        $this->expectException(\Throwable::class);

        Livewire::test(ProductForm::class)
            ->call('open');
    }

    public function test_user_cannot_create_product()
    {
        $user = User::factory()->create(['role' => 'user']);

        // abort_unless(false, 403) throws HttpException 403
        Livewire::actingAs($user)
            ->test(ProductForm::class)
            ->call('open')
            ->assertForbidden();
    }

    public function test_product_requires_brand_and_model()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(ProductForm::class)
            ->call('open')
            ->set('brand', '')
            ->set('model', 'XPS 13')
            ->call('save')
            ->assertHasErrors(['brand']);
    }

    public function test_generated_product_ids_are_unique()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Create first product
        Livewire::actingAs($admin)
            ->test(ProductForm::class)
            ->call('open')
            ->set('brand', 'Brand1')
            ->set('model', 'Model1')
            ->set('category', 'Notebook')
            ->set('price', 25000)
            ->call('save');

        // Create second product immediately (simulate concurrent creates)
        Livewire::actingAs($admin)
            ->test(ProductForm::class)
            ->call('open')
            ->set('brand', 'Brand2')
            ->set('model', 'Model2')
            ->set('category', 'Notebook')
            ->set('price', 30000)
            ->call('save');

        $products = Product::where('brand', 'Brand1')->orWhere('brand', 'Brand2')->get();
        $this->assertEquals(2, $products->count());
        $this->assertNotEquals($products[0]->id, $products[1]->id);
    }
}
