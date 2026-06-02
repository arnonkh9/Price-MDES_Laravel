<?php

namespace App\Imports;

use App\Models\Product;
use App\Support\GeneratesUUID;
use App\Support\Specs;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToCollection, WithHeadingRow
{
    public int $imported = 0;
    public int $skipped = 0;

    /**
     * Map of normalised heading => product column.
     */
    public array $errors = [];

    /**
     * Dry-run mode: validate without saving to DB
     */
    public bool $dryRun = false;
    public array $previewRows = [];

    /**
     * Map of normalised heading => product column.
     */
    private array $coreMap = [
        'id' => 'id',
        'category' => 'category',
        'brand' => 'brand',
        'model' => 'model',
        'price' => 'price',
        'pricedate' => 'price_date',
        'price_date' => 'price_date',
        'priceunit' => 'price_unit',
        'price_unit' => 'price_unit',
        'pricesource' => 'price_source',
        'price_source' => 'price_source',
        'priceurl' => 'price_url',
        'price_url' => 'price_url',
    ];

    public function collection(Collection $rows): void
    {
        // Build a lookup of spec field name (normalised) => canonical field label.
        $specFields = [];
        foreach (Specs::groups() as $g) {
            foreach ($g['fields'] as $f) {
                $specFields[$this->norm($f)] = $f;
            }
        }

        // Load valid categories
        $validCategories = \App\Models\Category::pluck('slug')->toArray();

        // Wrap entire import in transaction for atomicity
        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {
                try {
                    $data = $row->toArray();

                    $attrs = ['specs' => []];
                    foreach ($data as $key => $value) {
                        $nkey = $this->norm((string) $key);
                        if ($value === null || $value === '') {
                            continue;
                        }
                        if (isset($this->coreMap[$nkey])) {
                            $attrs[$this->coreMap[$nkey]] = $value;
                        } elseif (isset($specFields[$nkey])) {
                            $attrs['specs'][$specFields[$nkey]] = (string) $value;
                        }
                    }

                    // VALIDATION: Require at least brand + model
                    if (empty($attrs['brand']) || empty($attrs['model'])) {
                        $this->recordSkip($index + 1, 'ขาดข้อมูล brand หรือ model', $attrs);
                        continue;
                    }

                    // VALIDATION: Category must exist
                    $category = $attrs['category'] ?? 'Notebook';
                    if (!in_array($category, $validCategories)) {
                        $this->recordSkip($index + 1, "หมวดหมู่ '{$category}' ไม่มีอยู่ในระบบ", array_merge($attrs, ['category' => $category]));
                        continue;
                    }

                    // VALIDATION: Price must be numeric and non-negative
                    if (isset($attrs['price'])) {
                        if (!is_numeric($attrs['price'])) {
                            $this->recordSkip($index + 1, 'ราคาต้องเป็นตัวเลข: ' . $attrs['price'], array_merge($attrs, ['category' => $category, 'price' => null]));
                            continue;
                        }
                        if ((float) $attrs['price'] < 0) {
                            $this->recordSkip($index + 1, 'ราคาติดลบไม่ได้: ' . $attrs['price'], array_merge($attrs, ['category' => $category, 'price' => null]));
                            continue;
                        }
                    }

                    $id = ! empty($attrs['id']) ? (string) $attrs['id'] : 'imp-' . Uuid::uuid4()->toString();

                    if ($this->dryRun) {
                        // In dry-run mode, just collect preview data without saving
                        $this->previewRows[] = $this->makePreviewRow($index + 1, [
                            'id' => $id,
                            'brand' => $attrs['brand'],
                            'model' => $attrs['model'],
                            'category' => $category,
                            'price' => (float) ($attrs['price'] ?? 0),
                            'specs' => $attrs['specs'],
                        ], 'valid', '');
                    } else {
                        // Real import mode: save to database
                        unset($attrs['id']);

                        $product = Product::updateOrCreate(['id' => $id], [
                            'category' => $category,
                            'brand' => $attrs['brand'],
                            'model' => $attrs['model'],
                            'price' => (float) ($attrs['price'] ?? 0),
                            'price_unit' => $attrs['price_unit'] ?? 'บาท/เครื่อง',
                            'price_date' => $attrs['price_date'] ?? null,
                            'price_source' => $attrs['price_source'] ?? null,
                            'price_url' => $attrs['price_url'] ?? null,
                            'specs' => $attrs['specs'],
                        ]);

                        $product->histories()->create([
                            'date' => $attrs['price_date'] ?? null,
                            'user' => auth()->user()?->name ?? 'system',
                            'action' => 'นำเข้าจากไฟล์ Excel',
                            'detail' => 'นำเข้า ' . $product->model,
                            'source' => 'Excel',
                        ]);
                    }

                    $this->imported++;

                } catch (\InvalidArgumentException $e) {
                    $this->recordSkip($index + 1, $e->getMessage(), $attrs ?? []);
                } catch (\Exception $e) {
                    $this->recordSkip($index + 1, 'เกิดข้อผิดพลาด - ' . $e->getMessage(), $attrs ?? [], 'error');
                }
            }

            \Illuminate\Support\Facades\DB::commit();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error("ProductsImport: Transaction failed - " . $e->getMessage());
            $this->errors[] = "Fatal error: " . $e->getMessage();
            throw $e;
        }
    }

    /**
     * Record a skipped row: bump the counter, log it, collect the error, and
     * — in dry-run mode — append an "invalid" preview row so the user can see
     * which rows will be skipped before confirming the import.
     */
    private function recordSkip(int $rowNum, string $message, array $attrs = [], string $level = 'warning'): void
    {
        $this->skipped++;
        $full = "Row {$rowNum}: {$message}";
        \Illuminate\Support\Facades\Log::{$level}("ProductsImport: {$full}");
        $this->errors[] = $full;

        if ($this->dryRun) {
            $this->previewRows[] = $this->makePreviewRow($rowNum, $attrs, 'invalid', $message);
        }
    }

    private function makePreviewRow(int $rowNum, array $attrs, string $status, string $error = ''): array
    {
        return [
            'row' => $rowNum,
            'id' => $attrs['id'] ?? '—',
            'brand' => $attrs['brand'] ?? '—',
            'model' => $attrs['model'] ?? '—',
            'category' => $attrs['category'] ?? '—',
            'price' => isset($attrs['price']) ? number_format($attrs['price']) . ' ฿' : '—',
            'specCount' => count($attrs['specs'] ?? []),
            'status' => $status,
            'error' => $error,
        ];
    }

    private function norm(string $s): string
    {
        return strtolower(preg_replace('/[\s_\-]+/', '', trim($s)));
    }
}
