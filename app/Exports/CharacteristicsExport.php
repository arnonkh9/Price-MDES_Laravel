<?php

namespace App\Exports;

use App\Models\CharacteristicsTemplate;
use App\Support\Specs;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Font;

class CharacteristicsExport implements FromArray, WithColumnWidths, WithTitle, WithStyles
{
    public function __construct(private CharacteristicsTemplate $spec) {}

    public function title(): string
    {
        return 'คุณลักษณะ: ' . substr($this->spec->name, 0, 31);
    }

    public function columnWidths(): array
    {
        return ['A' => 30, 'B' => 50];
    }

    public function array(): array
    {
        $rows = [];
        $rows[] = ['ข้อกำหนดคุณลักษณะ', ''];
        $rows[] = ['ชื่อ: ' . $this->spec->name, ''];
        $rows[] = ['ประเภท: ' . Specs::label($this->spec->category), ''];
        $rows[] = ['ปี พ.ศ.: ' . $this->spec->year, ''];
        $rows[] = ['เดือน: ' . ($this->spec->month ? Specs::monthLabel($this->spec->month) : '-'), ''];
        $rows[] = ['วงเงิน: ' . ($this->spec->budget ? number_format((float)$this->spec->budget) . ' บาท' : '-'), ''];
        $rows[] = ['สร้างโดย: ' . $this->spec->created_by, ''];
        $rows[] = ['วันที่: ' . $this->spec->created_date, ''];
        $rows[] = ['', ''];

        if (!empty($this->spec->specs)) {
            $rows[] = ['คุณลักษณะ / ข้อกำหนด', 'รายละเอียด'];
            foreach ($this->spec->specs as $key => $value) {
                $rows[] = [$key, $value];
            }
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle($sheet->calculateWorksheetDimension())->getFont()->setName('TH Sarabun New');
        return [];
    }
}
