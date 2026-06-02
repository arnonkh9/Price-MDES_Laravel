<?php

namespace Tests\Feature;

use App\Imports\ProductsImport;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class MockRow
{
    public function __construct(private array $data) {}

    public function toArray(): array
    {
        return $this->data;
    }
}

class ProductsImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Import validation checks category against DB — seed categories first
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\CategorySeeder']);
    }

    public function test_import_valid_products()
    {
        $rows = new Collection([
            new MockRow(['id' => 'test-001', 'brand' => 'Dell', 'model' => 'XPS 13', 'category' => 'Notebook', 'price' => 45000]),
            new MockRow(['id' => 'test-002', 'brand' => 'ASUS', 'model' => 'VivoBook', 'category' => 'Notebook', 'price' => 35000]),
        ]);

        $import = new ProductsImport();
        $import->collection($rows);

        $this->assertEquals(2, $import->imported);
        $this->assertEquals(0, $import->skipped);
        $this->assertEquals(0, count($import->errors));

        $this->assertTrue(Product::where('brand', 'Dell')->exists());
        $this->assertTrue(Product::where('brand', 'ASUS')->exists());
    }

    public function test_import_skips_rows_without_brand_or_model()
    {
        $rows = new Collection([
            new MockRow(['brand' => 'Dell', 'model' => 'XPS', 'category' => 'Notebook']),
            new MockRow(['brand' => '', 'model' => 'Incomplete', 'category' => 'Notebook']),
            new MockRow(['brand' => 'ASUS', 'model' => '', 'category' => 'Notebook']),
        ]);

        $import = new ProductsImport();
        $import->collection($rows);

        $this->assertEquals(1, $import->imported);
        $this->assertEquals(2, $import->skipped);
        $this->assertGreaterThan(0, count($import->errors));
    }

    public function test_import_includes_new_fields_in_fillable()
    {
        $product = Product::factory()->create([
            'price_source' => 'TestSource',
            'price_url' => 'https://test.com',
        ]);

        $this->assertNotNull($product);
        $this->assertEquals('TestSource', $product->price_source);
        $this->assertEquals('https://test.com', $product->price_url);
    }

    public function test_import_class_has_error_collection()
    {
        $import = new ProductsImport();

        $this->assertIsArray($import->errors);
        $this->assertTrue(property_exists($import, 'errors'));
    }

    public function test_dry_run_preview_includes_invalid_rows()
    {
        // Regression: dry-run preview used to drop skipped rows, so the
        // preview screen always reported 0 skipped even when rows were invalid.
        $rows = new Collection([
            new MockRow(['brand' => 'Dell', 'model' => 'GOOD', 'category' => 'Notebook', 'price' => 15000]),
            new MockRow(['brand' => '', 'model' => 'NO-BRAND', 'category' => 'Notebook']),
            new MockRow(['brand' => 'HP', 'model' => 'BAD-PRICE', 'category' => 'Notebook', 'price' => 'notanumber']),
            new MockRow(['brand' => 'ACER', 'model' => 'BAD-CAT', 'category' => 'DoesNotExist', 'price' => 5000]),
        ]);

        $import = new ProductsImport();
        $import->dryRun = true;
        $import->collection($rows);

        // Nothing should be persisted in dry-run mode
        $this->assertEquals(0, Product::count());

        // Every row (valid + invalid) must appear in the preview
        $this->assertCount(4, $import->previewRows);

        $valid = array_filter($import->previewRows, fn ($r) => $r['status'] === 'valid');
        $invalid = array_filter($import->previewRows, fn ($r) => $r['status'] !== 'valid');
        $this->assertCount(1, $valid);
        $this->assertCount(3, $invalid);

        // Invalid rows carry a human-readable error for the tooltip
        foreach ($invalid as $r) {
            $this->assertNotSame('', $r['error']);
        }
    }

    public function test_import_creates_histories()
    {
        $rows = new Collection([
            new MockRow(['brand' => 'TestBrand', 'model' => 'TestModel', 'category' => 'Notebook', 'price' => 25000]),
        ]);

        $import = new ProductsImport();
        $import->collection($rows);

        $this->assertEquals(1, $import->imported);
        $product = Product::where('brand', 'TestBrand')->first();
        $this->assertNotNull($product);
        $this->assertGreaterThan(0, $product->histories()->count());
    }
}
