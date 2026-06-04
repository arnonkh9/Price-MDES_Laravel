<?php

namespace Tests\Feature;

use App\Exports\BulkCharacteristicsExport;
use App\Imports\CharacteristicsImport;
use App\Models\CharacteristicsTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class BulkCharacteristicsExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Import round-trip validates category against DB slugs
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\CategorySeeder']);
    }

    public function test_export_uses_import_column_layout()
    {
        $specs = collect([
            CharacteristicsTemplate::factory()->create([
                'category' => 'Notebook',
                'budget'   => 250000,
                'specs'    => ['Processor' => 'Intel Core i5', 'RAM' => '8GB'],
            ]),
            CharacteristicsTemplate::factory()->create([
                'category' => 'AIO',
                'budget'   => 180000,
                'specs'    => ['Processor' => 'Intel Core i7'],
            ]),
        ]);

        $rows = (new BulkCharacteristicsExport($specs))->array();

        // Header matches the import sample (core columns first, then Spec N)
        $header = $rows[0];
        $this->assertSame(
            ['name', 'category', 'year', 'month', 'budget', 'created_date', 'created_by', 'purpose'],
            array_slice($header, 0, 8)
        );
        // maxSpecs = 2 → two spec columns (numeric headers matching canonical spec field keys)
        $this->assertSame(1, $header[8]);
        $this->assertSame(2, $header[9]);

        // Data row: category is a raw slug, budget has no comma, spec cells hold the value
        $first = $rows[1];
        $this->assertSame('Notebook', $first[1]);
        $this->assertSame('250000', $first[4]);
        $this->assertStringNotContainsString(',', $first[4]);
        $this->assertSame('Intel Core i5', $first[8]);
        $this->assertSame('8GB', $first[9]);

        // Spec with fewer items gets a blank trailing cell
        $second = $rows[2];
        $this->assertSame('Intel Core i7', $second[8]);
        $this->assertSame('', $second[9]);
    }

    public function test_exported_rows_can_be_reimported()
    {
        $specs = collect([
            CharacteristicsTemplate::factory()->create([
                'category' => 'Notebook',
                'specs'    => ['Processor' => 'Intel Core i5', 'RAM' => '8GB'],
            ]),
        ]);

        $rows = (new BulkCharacteristicsExport($specs))->array();

        // Feed exported rows (heading + data) into the importer (dry-run).
        // Each row exposes toArray() like Maatwebsite's heading-row collection.
        $header = $rows[0];
        $collection = new Collection(
            collect(array_slice($rows, 1))->map(function ($row) use ($header) {
                return new class(array_combine($header, $row)) {
                    public function __construct(private array $data) {}
                    public function toArray(): array { return $this->data; }
                };
            })->all()
        );

        $import = new CharacteristicsImport();
        $import->dryRun = true;
        $import->collection($collection);

        $this->assertSame(0, $import->skipped);
        $this->assertCount(1, $import->previewRows);
        $this->assertSame('valid', $import->previewRows[0]['status']);
    }
}
