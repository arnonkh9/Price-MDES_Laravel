<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\CategorySeeder']);
    }

    public function test_guest_cannot_export_products()
    {
        $response = $this->get('/products/export');

        $this->assertTrue(
            $response->status() === 302 || $response->status() === 401,
            'Guest should be redirected or unauthorized'
        );
    }

    public function test_authenticated_user_can_export_products()
    {
        $user = User::factory()->create(['role' => 'admin']);
        Product::factory()->count(3)->create();

        $response = $this->actingAs($user)->get('/products/export');

        $response->assertOk();
        // Content-Disposition may encode Thai filename differently; just verify it's a download
        $this->assertNotNull($response->headers->get('content-disposition'));
        $this->assertStringContainsString('.csv', $response->headers->get('content-disposition'));
    }

    public function test_exported_csv_contains_all_fields()
    {
        $user = User::factory()->create(['role' => 'admin']);
        Product::factory()->create([
            'brand' => 'TestBrand',
            'model' => 'TestModel',
            'price' => 25000,
            'price_source' => 'Test Source',
            'price_url' => 'https://test.com',
        ]);

        $response = $this->actingAs($user)->get('/products/export');

        // streamDownload content accessible via streamedContent()
        $content = $response->streamedContent();

        $this->assertStringContainsString('TestBrand', $content);
        $this->assertStringContainsString('TestModel', $content);
        $this->assertStringContainsString('Test Source', $content);
        $this->assertStringContainsString('https://test.com', $content);
    }

    public function test_exported_csv_includes_header_row()
    {
        $user = User::factory()->create(['role' => 'admin']);
        Product::factory()->create();

        $response = $this->actingAs($user)->get('/products/export');
        $content = $response->streamedContent();

        // Check for header columns
        $this->assertStringContainsString('id', $content);
        $this->assertStringContainsString('brand', $content);
        $this->assertStringContainsString('model', $content);
        $this->assertStringContainsString('price', $content);
    }

    public function test_csv_export_has_utf8_bom()
    {
        $user = User::factory()->create(['role' => 'admin']);
        Product::factory()->create();

        $response = $this->actingAs($user)->get('/products/export');
        $content = $response->streamedContent();

        // UTF-8 BOM is EF BB BF in hex
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
    }
}
