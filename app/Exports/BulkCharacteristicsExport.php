<?php

namespace App\Exports;

use App\Support\Specs;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Font;

class BulkCharacteristicsExport implements FromArray, WithColumnWidths, WithTitle, WithStyles
{
    public function __construct(private Collection $characteristics) {}

    public function title(): string
    {
        return 'คุณลักษณะ';
    }

    public function columnWidths(): array
    {
        return ['A' => 25, 'B' => 20, 'C' => 15, 'D' => 15, 'E' => 20, 'F' => 50];
    }

    public function array(): array
    {
        $rows = [];
        $rows[] = ['รายการคุณลักษณะ', 'ประเภท', 'ปี พ.ศ.', 'เดือน', 'วงเงิน (บาท)', 'สร้างโดย'];

        foreach ($this->characteristics as $spec) {
            $rows[] = [
                $spec->name,
                Specs::label($spec->category),
                $spec->year ?: '',
                $spec->month ? Specs::monthLabel($spec->month) : '',
                $spec->budget ? number_format((float)$spec->budget) : '',
                $spec->created_by ?: '',
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle($sheet->calculateWorksheetDimension())->getFont()->setName('TH Sarabun New');
        return [];
    }
}
