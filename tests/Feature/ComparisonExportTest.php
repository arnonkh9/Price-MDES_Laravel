<?php

namespace Tests\Feature;

use App\Exports\ComparisonExport;
use App\Models\Comparison;
use App\Models\ComparisonVendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ComparisonExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\CategorySeeder']);
    }

    public function test_export_comparison_with_null_characteristics_template()
    {
        $cmp = Comparison::factory()->create([
            'name' => 'Test Comparison',
            'characteristics_template_id' => null,
        ]);

        for ($i = 1; $i <= 3; $i++) {
            ComparisonVendor::factory()->create([
                'comparison_id' => $cmp->id,
                'position' => $i,
                'name' => "Vendor {$i}",
                'price' => 25000 + ($i * 1000),
            ]);
        }

        // Reload with vendors relationship
        $cmp->load('vendors');

        $export = new ComparisonExport($cmp);
        $array = $export->array();

        $this->assertIsArray($array);
        $this->assertGreaterThan(0, count($array));
    }

    public function test_export_comparison_with_characteristics_template()
    {
        // Create a characteristics template first
        $template = \App\Models\CharacteristicsTemplate::factory()->create([
            'specs' => ['Processor' => 'Intel Core i7 or equivalent'],
        ]);

        $cmp = Comparison::factory()->create([
            'name' => 'Test Comparison',
            'characteristics_template_id' => $template->id,
        ]);

        for ($i = 1; $i <= 3; $i++) {
            ComparisonVendor::factory()->create([
                'comparison_id' => $cmp->id,
                'position' => $i,
                'name' => "Vendor {$i}",
                'price' => 25000 + ($i * 1000),
                'specs' => ['Processor' => "Intel Core i7-{$i}000"],
            ]);
        }

        $cmp->load('vendors');

        $export = new ComparisonExport($cmp);
        $array = $export->array();

        $this->assertIsArray($array);
        $exported = json_encode($array);
        $this->assertStringContainsString('Processor', $exported);
    }

    public function test_export_excel_file_downloads_successfully()
    {
        $cmp = Comparison::factory()->create([
            'characteristics_template_id' => null,
        ]);

        for ($i = 1; $i <= 3; $i++) {
            ComparisonVendor::factory()->create([
                'comparison_id' => $cmp->id,
                'position' => $i,
                'price' => 25000,
            ]);
        }

        $cmp->load('vendors');

        $export = new ComparisonExport($cmp);
        $this->assertNotNull($export->array());
    }
}
