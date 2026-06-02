<?php

namespace App\Exports;

use App\Models\Comparison;
use App\Models\CharacteristicsTemplate;
use App\Support\Specs;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Font;

class ComparisonExport implements FromArray, WithColumnWidths, WithTitle, WithStyles
{
    public function __construct(private Comparison $cmp) {}

    public function title(): string
    {
        // Excel sheet names max 31 chars
        return substr($this->cmp->name, 0, 31) ?: 'เปรียบเทียบ 3 เจ้า';
    }

    public function columnWidths(): array
    {
        return ['A' => 30, 'B' => 36, 'C' => 34, 'D' => 34, 'E' => 34];
    }

    public function array(): array
    {
        $cmp = $this->cmp;
        $spec = $cmp->characteristics_template_id ? CharacteristicsTemplate::find($cmp->characteristics_template_id) : null;
        $v = $cmp->vendors->values();
        $fmt = fn ($n) => $n ? number_format((float) $n) : '';
        $vendor = fn ($i) => $v->get($i);

        $rows = [];
        $rows[] = ['ตารางเปรียบเทียบราคาและคุณลักษณะ 3 เจ้า', '', '', '', ''];
        $rows[] = ["ชื่อการเปรียบเทียบ: {$cmp->name}", '', '', '', ''];
        $rows[] = [
            'ประเภท: '.Specs::label($cmp->category),
            'ปี พ.ศ.: '.$cmp->year,
            'เดือน: '.($cmp->month ? Specs::monthLabel($cmp->month) : '-'),
            'สร้างโดย: '.$cmp->created_by,
            'วันที่: '.$cmp->created_date,
        ];
        if ($spec) {
            $rows[] = ["คุณลักษณะพื้นฐานอ้างอิง: {$spec->name}", 'วงเงิน: '.$fmt($spec->budget).' บาท', '', '', ''];
        }
        $rows[] = ['', '', '', '', ''];

        $rows[] = ['รายการ / ข้อกำหนด', $spec ? 'คุณลักษณะพื้นฐาน' : '', $vendor(0)?->name ?: 'เจ้าที่ 1', $vendor(1)?->name ?: 'เจ้าที่ 2', $vendor(2)?->name ?: 'เจ้าที่ 3'];
        $rows[] = ['แบรนด์', '', $vendor(0)?->brand ?? '', $vendor(1)?->brand ?? '', $vendor(2)?->brand ?? ''];
        $rows[] = ['รุ่น / โมเดล', '', $vendor(0)?->model ?? '', $vendor(1)?->model ?? '', $vendor(2)?->model ?? ''];
        $rows[] = ['ราคาเสนอ (บาท)', $spec ? 'วงเงิน '.$fmt($spec->budget).' ฿' : '', $fmt($vendor(0)?->price), $fmt($vendor(1)?->price), $fmt($vendor(2)?->price)];
        $rows[] = ['', '', '', '', ''];

        // แถวสเปค = union ของ key จากสเปคอ้างอิง + vendors (label = ชื่อ key)
        $active = collect(Specs::comparisonFieldKeys($spec?->specs, $v->pluck('specs')))
            ->filter(function ($f) use ($spec, $v) {
                if ($spec && ! empty($spec->specs[$f] ?? null)) {
                    return true;
                }
                return $v->contains(fn ($vd) => ! empty($vd->specs[$f] ?? null));
            });
        if ($active->isNotEmpty()) {
            $rows[] = ['[ข้อมูลจำเพาะ]', '', '', '', ''];
            foreach ($active as $field) {
                $rows[] = [
                    $field,
                    $spec?->specs[$field] ?? '',
                    $vendor(0)?->specs[$field] ?? '',
                    $vendor(1)?->specs[$field] ?? '',
                    $vendor(2)?->specs[$field] ?? '',
                ];
            }
            $rows[] = ['', '', '', '', ''];
        }

        // Summary
        $rows[] = ['สรุปผล', '', '', '', ''];
        $rows[] = ['ราคาเสนอ (บาท)', '', $fmt($vendor(0)?->price), $fmt($vendor(1)?->price), $fmt($vendor(2)?->price)];
        $prices = $v->map(fn ($vd) => (float) $vd->price)->filter(fn ($p) => $p > 0);
        $min = $prices->min();
        $rows[] = [
            'ราคาต่ำสุด', '',
            ($vendor(0) && (float) $vendor(0)->price === (float) $min) ? '✓ ต่ำสุด' : '',
            ($vendor(1) && (float) $vendor(1)->price === (float) $min) ? '✓ ต่ำสุด' : '',
            ($vendor(2) && (float) $vendor(2)->price === (float) $min) ? '✓ ต่ำสุด' : '',
        ];
        if ($cmp->notes) {
            $rows[] = ['หมายเหตุ', $cmp->notes, '', '', ''];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle($sheet->calculateWorksheetDimension())->getFont()->setName('TH Sarabun New');
        return [];
    }
}
