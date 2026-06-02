<?php

namespace App\Exports;

use App\Models\Category;
use App\Support\Specs;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SampleCharacteristicsExport implements FromArray, WithColumnWidths, WithEvents, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'ตัวอย่าง_คุณลักษณะ';
    }

    public function columnWidths(): array
    {
        return ['A' => 25, 'B' => 20, 'C' => 10, 'D' => 10, 'E' => 15, 'F' => 18, 'G' => 15, 'H' => 25, 'I' => 35, 'J' => 35];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle($sheet->calculateWorksheetDimension())->getFont()->setName('TH Sarabun New');
        return [];
    }

    public function array(): array
    {
        return [
            ['name', 'category', 'year', 'month', 'budget', 'created_date', 'created_by', 'purpose', 'Spec 1', 'Spec 2'],
            ['คำขอจัดซื้อโน้ตบุ๊ก 2569', 'Notebook', '2569', '05', '250000', '2026-05-26', 'admin', 'ข้อกำหนดโน้ตบุ๊ก', 'Processor: Intel Core i5', 'RAM: 8GB'],
            ['ข้อกำหนดคอมพิวเตอร์ AIO 2569', 'AIO', '2569', '05', '180000', '2026-05-26', 'admin', 'ข้อกำหนด AIO', 'Processor: Intel Core i7', 'RAM: 16GB'],
        ];
    }

    /**
     * Add Excel dropdown (data validation) to category / year / month columns
     * so users pick valid values instead of typing slugs by hand.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $categories = Category::orderBy('position')->pluck('slug')->implode(',');
                $years      = implode(',', Specs::years());
                $months     = '01,02,03,04,05,06,07,08,09,10,11,12';

                $applyDropdown = function (string $col, string $list) use ($sheet) {
                    for ($row = 2; $row <= 30; $row++) {
                        $validation = $sheet->getCell("{$col}{$row}")->getDataValidation();
                        $validation->setType(DataValidation::TYPE_LIST);
                        $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                        $validation->setAllowBlank(true);
                        $validation->setShowInputMessage(true);
                        $validation->setShowErrorMessage(true);
                        $validation->setShowDropDown(true);
                        $validation->setFormula1('"' . $list . '"');
                    }
                };

                $applyDropdown('B', $categories); // category
                $applyDropdown('C', $years);      // year
                $applyDropdown('D', $months);     // month
            },
        ];
    }
}
