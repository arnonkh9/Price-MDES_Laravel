<?php

namespace App\Exports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SampleProductsExport implements FromArray, WithColumnWidths, WithEvents, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'ตัวอย่าง_สินค้า';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15, 'B' => 20, 'C' => 15, 'D' => 30, 'E' => 12,
            'F' => 14, 'G' => 16, 'H' => 15, 'I' => 25,
            'J' => 30, 'K' => 30, 'L' => 30, 'M' => 30, 'N' => 30,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle($sheet->calculateWorksheetDimension())->getFont()->setName('TH Sarabun New');
        return [];
    }

    public function array(): array
    {
        return [
            ['id', 'category', 'brand', 'model', 'price', 'price_unit', 'price_date', 'price_source', 'price_url', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15'],
            ['', 'Notebook', 'Dell', 'Latitude 5540', '35900', 'บาท/เครื่อง', '2569-05-28', 'Website', 'https://www.dell.com', 'Intel Core i5-1345U', '16 GB DDR4', '512 GB SSD NVMe', '15.6" FHD IPS', 'Intel Iris Xe', 'Windows 11 Pro', 'Wi-Fi 6E + BT 5.3', '3-cell 54 Wh', '1.58 kg', '3 ปี', '', '', '', '', ''],
            ['', 'Server', 'HPE', 'ProLiant DL380 Gen11', '189000', 'บาท/เครื่อง', '2569-05-28', 'Website', 'https://www.hpe.com', 'Intel Xeon Silver 4410Y', '32 GB DDR5 RDIMM', '2× 960 GB SSD SAS', 'Rack 2U', 'HPE iLO 6 Standard', 'ไม่มี OS', '4× 1GbE', 'HPE 800W Flex Slot', '', '3 ปี', '', '', '', '', ''],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $categories = Category::orderBy('position')->pluck('slug')->implode(',');

                for ($row = 2; $row <= 30; $row++) {
                    $validation = $sheet->getCell("B{$row}")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                    $validation->setAllowBlank(true);
                    $validation->setShowInputMessage(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setShowDropDown(true);
                    $validation->setFormula1('"' . $categories . '"');
                }
            },
        ];
    }
}
