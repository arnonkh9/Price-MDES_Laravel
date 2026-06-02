<?php

namespace App\Imports;

use App\Models\CharacteristicsTemplate;
use App\Models\CharacteristicsTemplateHistory;
use App\Support\Specs;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CharacteristicsImport implements ToCollection, WithHeadingRow
{
    public int $imported = 0;
    public int $skipped = 0;
    public array $errors = [];

    /** When true: validate only — do NOT write to DB, populate $previewRows instead. */
    public bool $dryRun = false;

    /** Populated during dry-run: [{name,category,year,month,budget,specCount,status,error}] */
    public array $previewRows = [];

    /**
     * Map of normalised heading => characteristics column.
     */
    private array $coreMap = [
        'name' => 'name',
        'category' => 'category',
        'year' => 'year',
        'month' => 'month',
        'budget' => 'budget',
        'created_date' => 'created_date',
        'created_by' => 'created_by',
        'purpose' => 'purpose',
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

        // Build a normalised core-map lookup so headings like "created_date"
        // (which norm() turns into "createddate") still match their column.
        $normCoreMap = [];
        foreach ($this->coreMap as $heading => $column) {
            $normCoreMap[$this->norm($heading)] = $column;
        }

        // Load valid categories
        $validCategories = \App\Models\Category::pluck('slug')->toArray();

        // Load valid years and months
        $validYears = Specs::years();
        $validMonths = range(1, 12);

        // Wrap entire import in transaction for atomicity (skip for dry-run)
        if (!$this->dryRun) {
            \Illuminate\Support\Facades\DB::beginTransaction();
        }

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
                        if (isset($normCoreMap[$nkey])) {
                            $attrs[$normCoreMap[$nkey]] = $value;
                        } elseif (isset($specFields[$nkey])) {
                            $attrs['specs'][$specFields[$nkey]] = (string) $value;
                        } else {
                            // Accept any other column as a spec field (e.g., "Spec 1", "Spec 2")
                            $attrs['specs'][(string) $key] = (string) $value;
                        }
                    }

                    // VALIDATION: Name is required and min 3 chars
                    if (empty($attrs['name']) || strlen($attrs['name']) < 3) {
                        $this->skipped++;
                        $error = "ชื่อต้องมีอย่างน้อย 3 ตัวอักษร";
                        \Illuminate\Support\Facades\Log::warning("CharacteristicsImport row " . ($index + 1) . ": {$error}");
                        $this->errors[] = "Row " . ($index + 1) . ": {$error}";
                        if ($this->dryRun) {
                            $this->previewRows[] = $this->makePreviewRow($index, $attrs, 'invalid', $error);
                        }
                        continue;
                    }

                    // VALIDATION: Category must exist
                    $category = $attrs['category'] ?? '';
                    if (!in_array($category, $validCategories)) {
                        $this->skipped++;
                        $error = "หมวดหมู่ '{$category}' ไม่ถูกต้อง";
                        \Illuminate\Support\Facades\Log::warning("CharacteristicsImport row " . ($index + 1) . ": {$error}");
                        $this->errors[] = "Row " . ($index + 1) . ": {$error}";
                        if ($this->dryRun) {
                            $this->previewRows[] = $this->makePreviewRow($index, $attrs, 'invalid', $error);
                        }
                        continue;
                    }

                    // VALIDATION: Year must be valid (if provided)
                    if (!empty($attrs['year'])) {
                        if (!in_array((string) $attrs['year'], $validYears)) {
                            $this->skipped++;
                            $error = "ปี '{$attrs['year']}' ไม่ถูกต้อง";
                            \Illuminate\Support\Facades\Log::warning("CharacteristicsImport row " . ($index + 1) . ": {$error}");
                            $this->errors[] = "Row " . ($index + 1) . ": {$error}";
                            if ($this->dryRun) {
                                $this->previewRows[] = $this->makePreviewRow($index, $attrs, 'invalid', $error);
                            }
                            continue;
                        }
                    }

                    // VALIDATION: Month must be 01-12 (if provided)
                    if (!empty($attrs['month'])) {
                        $month = (int) $attrs['month'];
                        if ($month < 1 || $month > 12) {
                            $this->skipped++;
                            $error = "เดือนต้องอยู่ระหว่าง 01-12";
                            \Illuminate\Support\Facades\Log::warning("CharacteristicsImport row " . ($index + 1) . ": {$error}");
                            $this->errors[] = "Row " . ($index + 1) . ": {$error}";
                            if ($this->dryRun) {
                                $this->previewRows[] = $this->makePreviewRow($index, $attrs, 'invalid', $error);
                            }
                            continue;
                        }
                        // Pad to 2 digits
                        $attrs['month'] = str_pad($month, 2, '0', STR_PAD_LEFT);
                    }

                    // VALIDATION: Budget must be numeric and >= 0 (if provided)
                    if (isset($attrs['budget'])) {
                        $budget = (float) $attrs['budget'];
                        if ($budget < 0) {
                            $this->skipped++;
                            $error = "งบประมาณต้องไม่ติดลบ";
                            \Illuminate\Support\Facades\Log::warning("CharacteristicsImport row " . ($index + 1) . ": {$error}");
                            $this->errors[] = "Row " . ($index + 1) . ": {$error}";
                            if ($this->dryRun) {
                                $this->previewRows[] = $this->makePreviewRow($index, $attrs, 'invalid', $error);
                            }
                            continue;
                        }
                        $attrs['budget'] = $budget;
                    }

                    // ── Dry-run: record valid row without saving ──────────────
                    if ($this->dryRun) {
                        $this->previewRows[] = $this->makePreviewRow($index, $attrs, 'valid');
                        $this->imported++;
                        continue;
                    }

                    // Generate ID: sp-XXX
                    $attrs['id'] = 'sp-' . str_pad($this->getNextId(), 3, '0', STR_PAD_LEFT);

                    // Create characteristics
                    CharacteristicsTemplate::create($attrs);

                    // Create history record
                    CharacteristicsTemplateHistory::create([
                        'characteristics_template_id' => $attrs['id'],
                        'date' => now()->format('Y-m-d'),
                        'user' => auth()->user()->name ?? 'System',
                        'action' => 'สร้าง',
                        'detail' => 'นำเข้าจาก Excel',
                        'source' => 'Excel',
                        'url' => null,
                    ]);

                    $this->imported++;
                } catch (\Exception $e) {
                    $this->skipped++;
                    $error = "Row " . ($index + 1) . ": " . $e->getMessage();
                    \Illuminate\Support\Facades\Log::error("CharacteristicsImport: {$error}");
                    $this->errors[] = $error;
                    continue;
                }
            }

            if (!$this->dryRun) {
                \Illuminate\Support\Facades\DB::commit();
            }
        } catch (\Exception $e) {
            if (!$this->dryRun) {
                \Illuminate\Support\Facades\DB::rollBack();
            }
            throw $e;
        }
    }

    /**
     * Build a preview row record for dry-run mode.
     */
    private function makePreviewRow(int $index, array $attrs, string $status, string $error = ''): array
    {
        return [
            'row'       => $index + 2,  // +2 = 1-based + heading row
            'name'      => $attrs['name'] ?? '',
            'category'  => $attrs['category'] ?? '',
            'year'      => $attrs['year'] ?? '',
            'month'     => $attrs['month'] ?? '',
            'budget'    => $attrs['budget'] ?? null,
            'specCount' => count($attrs['specs'] ?? []),
            'status'    => $status,
            'error'     => $error,
        ];
    }

    /**
     * Normalize column heading for comparison.
     */
    private function norm(string $val): string
    {
        return strtolower(preg_replace('/[^a-z0-9]+/i', '', $val));
    }

    /**
     * Get next characteristics ID number.
     */
    private function getNextId(): int
    {
        $last = CharacteristicsTemplate::where('id', 'like', 'sp-%')
            ->orderBy('id', 'desc')
            ->first();

        if (!$last) {
            return 1;
        }

        $lastNum = (int) substr($last->id, 3);
        return $lastNum + 1;
    }
}
