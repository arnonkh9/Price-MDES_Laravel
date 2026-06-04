<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Font;

/**
 * Bulk export of characteristics templates in the SAME column layout as the
 * import file (see SampleCharacteristicsExport), so an exported file can be
 * re-imported via CharacteristicsImport (round-trip).
 *
 *   name | category | year | month | budget | created_date | created_by | purpose | Spec 1 | Spec 2 | …
 *
 * - category is the raw slug (import validates slugs, not Thai labels)
 * - budget is a raw number (no thousands separator — a comma breaks import)
 * - each "Spec N" cell holds one spec item formatted as "key: value"
 */
class BulkCharacteristicsExport implements FromArray, WithColumnWidths, WithTitle, WithStyles
{
    /** Core columns shared with the import sample. */
    private const CORE_HEADERS = [
        'name', 'category', 'year', 'month', 'budget', 'created_date', 'created_by', 'purpose',
    ];

    public function __construct(private Collection $characteristics) {}

    public function title(): string
    {
        return 'คุณลักษณะ';
    }

    public function columnWidths(): array
    {
        // Core columns A–H
        $widths = ['A' => 25, 'B' => 18, 'C' => 10, 'D' => 10, 'E' => 15, 'F' => 16, 'G' => 15, 'H' => 25];

        // Spec columns I onward — give each a generous width
        $maxSpecs = $this->maxSpecCount();
        for ($i = 0; $i < $maxSpecs; $i++) {
            $col = $this->columnLetter(count(self::CORE_HEADERS) + $i); // 8 = column I
            $widths[$col] = 35;
        }

        return $widths;
    }

    public function array(): array
    {
        $maxSpecs = $this->maxSpecCount();

        // Header row
        $header = self::CORE_HEADERS;
        for ($i = 1; $i <= $maxSpecs; $i++) {
              
            //$header[] = 'Spec ' . $i;
            $header[] = $i;
        }

        $rows = [$header];

        foreach ($this->characteristics as $spec) {
            $row = [
                $spec->name,
                $spec->category,            // raw slug for round-trip import
                $spec->year ?: '',
                $spec->month ?: '',
                $spec->budget !== null && $spec->budget !== '' ? (string) (float) $spec->budget : '',
                $spec->created_date ?: '',
                $spec->created_by ?: '',
                $spec->purpose ?: '',
            ];

            // Spec items as "key: value" cells, padded to $maxSpecs columns
            $specItems = [];
            foreach (($spec->specs ?? []) as $key => $value) {
                $value = is_array($value) ? implode(', ', $value) : $value;
                
                //$specItems[] = $key . ': ' . $value;
                $specItems[] = $value;
            }
            for ($i = 0; $i < $maxSpecs; $i++) {
                $row[] = $specItems[$i] ?? '';
            }

            $rows[] = $row;
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle($sheet->calculateWorksheetDimension())->getFont()->setName('TH Sarabun New');
        return [];
    }

    /** Largest number of spec items across the selected specs (min 1 so a Spec column always exists). */
    private function maxSpecCount(): int
    {
        $max = $this->characteristics
            ->map(fn ($spec) => is_array($spec->specs) ? count($spec->specs) : 0)
            ->max() ?? 0;

        return max(1, (int) $max);
    }

    /** 0-based column index → spreadsheet column letter (0 = A, 8 = I, 26 = AA). */
    private function columnLetter(int $index): string
    {
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1);
    }
}
