<?php

namespace App\Exports;

use App\Support\Reports;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Excel export for any report type — rows are sourced from the shared
 * Reports::build() so the spreadsheet matches the on-screen report exactly.
 */
class ReportExport implements FromArray, WithTitle, WithStyles
{
    public function __construct(private string $type, private array $filters = []) {}

    public function title(): string
    {
        // Excel sheet names max 31 chars
        return mb_substr(Reports::label($this->type), 0, 31);
    }

    public function array(): array
    {
        $report = Reports::build($this->type, $this->filters);

        $rows = [];
        $rows[] = array_map(fn ($c) => $c['label'], $report['columns']);
        foreach ($report['rows'] as $row) {
            $line = [];
            foreach ($report['columns'] as $col) {
                $line[] = $row[$col['key']] ?? '';
            }
            $rows[] = $line;
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle($sheet->calculateWorksheetDimension())->getFont()->setName('TH Sarabun New');

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
