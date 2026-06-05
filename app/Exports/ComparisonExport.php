<?php

namespace App\Exports;

use App\Models\Comparison;
use App\Models\CharacteristicsTemplate;
use App\Support\Specs;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ComparisonExport implements FromArray, WithColumnWidths, WithTitle, WithStyles
{
    public function __construct(private Comparison $cmp) {}

    /** จำนวนผู้ผลิต (อย่างน้อย 1 เพื่อให้ตารางมีคอลัมน์) */
    private function vendorCount(): int
    {
        return max(1, $this->cmp->vendors->count());
    }

    public function title(): string
    {
        // Excel sheet names max 31 chars
        return substr($this->cmp->name, 0, 31) ?: 'ตารางเปรียบเทียบราคา';
    }

    public function columnWidths(): array
    {
        // A = รายการ, B = คอลัมน์สเปคอ้างอิง, จากนั้นคอลัมน์ผู้ผลิตรายละ 34
        $widths = ['A' => 30, 'B' => 36];
        for ($k = 0; $k < $this->vendorCount(); $k++) {
            $col = Coordinate::stringFromColumnIndex(3 + $k); // C, D, E, ...
            $widths[$col] = 34;
        }
        return $widths;
    }

    public function array(): array
    {
        $cmp  = $this->cmp;
        $spec = $cmp->characteristics_template_id ? CharacteristicsTemplate::find($cmp->characteristics_template_id) : null;
        $v    = $cmp->vendors->values();
        $n    = $this->vendorCount();
        $fmt  = fn ($x) => $x ? number_format((float) $x) : '';

        // สร้างแถว: [คอลัมน์ A, คอลัมน์ B, แล้วต่อด้วย 1 ค่า/ผู้ผลิต]
        $row = function ($a, $b, callable $vendorCell) use ($v, $n) {
            $cells = [$a, $b];
            for ($i = 0; $i < $n; $i++) {
                $cells[] = $vendorCell($v->get($i), $i);
            }
            return $cells;
        };
        $blank = fn () => array_fill(0, 2 + $n, '');

        $rows = [];
        $rows[] = $row('ตารางเปรียบเทียบราคาและคุณลักษณะผู้ผลิต', '', fn () => '');
        $rows[] = $row("ชื่อการเปรียบเทียบ: {$cmp->name}", '', fn () => '');
        $rows[] = [
            'ประเภท: '.Specs::label($cmp->category),
            'ปี พ.ศ.: '.$cmp->year,
            'เดือน: '.($cmp->month ? Specs::monthLabel($cmp->month) : '-'),
            'สร้างโดย: '.$cmp->created_by,
            'วันที่: '.$cmp->created_date,
        ];
        if ($spec) {
            $rows[] = $row("คุณลักษณะพื้นฐานอ้างอิง: {$spec->name}", 'วงเงิน: '.$fmt($spec->budget).' บาท', fn () => '');
        }
        $rows[] = $blank();

        // หัวตาราง + ข้อมูลพื้นฐาน
        $rows[] = $row('รายการ / ข้อกำหนด', $spec ? 'คุณลักษณะพื้นฐาน' : '', fn ($vd, $i) => $vd?->name ?: 'เจ้าที่ '.($i + 1));
        $rows[] = $row('แบรนด์', '', fn ($vd) => $vd?->brand ?? '');
        $rows[] = $row('รุ่น / โมเดล', '', fn ($vd) => $vd?->model ?? '');
        $rows[] = $row('ราคาเสนอ (บาท)', $spec ? 'วงเงิน '.$fmt($spec->budget).' ฿' : '', fn ($vd) => $fmt($vd?->price));
        $rows[] = $blank();

        // แถวสเปค = union ของ key จากสเปคอ้างอิง + vendors
        $active = collect(Specs::comparisonFieldKeys($spec?->specs, $v->pluck('specs')))
            ->filter(function ($f) use ($spec, $v) {
                if ($spec && ! empty($spec->specs[$f] ?? null)) {
                    return true;
                }
                return $v->contains(fn ($vd) => ! empty($vd->specs[$f] ?? null));
            });
        if ($active->isNotEmpty()) {
            $rows[] = $row('[ข้อมูลจำเพาะ]', '', fn () => '');
            foreach ($active as $field) {
                $rows[] = $row($field, $spec?->specs[$field] ?? '', fn ($vd) => $vd?->specs[$field] ?? '');
            }
            $rows[] = $blank();
        }

        // สรุปผล
        $prices = $v->map(fn ($vd) => (float) $vd->price)->filter(fn ($p) => $p > 0);
        $min    = $prices->min();
        $rows[] = $row('สรุปผล', '', fn () => '');
        $rows[] = $row('ราคาเสนอ (บาท)', '', fn ($vd) => $fmt($vd?->price));
        $rows[] = $row('ราคาต่ำสุด', '', fn ($vd) => ($vd && (float) $vd->price === (float) $min && $min > 0) ? '✓ ต่ำสุด' : '');
        if ($cmp->notes) {
            $rows[] = $row('หมายเหตุ', $cmp->notes, fn () => '');
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle($sheet->calculateWorksheetDimension())->getFont()->setName('TH Sarabun New');
        return [];
    }
}
