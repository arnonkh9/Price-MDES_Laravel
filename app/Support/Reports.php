<?php

namespace App\Support;

use App\Models\CharacteristicsTemplate;
use App\Models\CharacteristicsTemplateHistory;
use App\Models\Comparison;
use App\Models\Product;
use App\Models\ProductEditHistory;

/**
 * Central aggregation for the Reports section. Each report is built once here
 * and reused by the on-screen page (ReportPage), the PDF export, and the Excel
 * export, so the three outputs always agree.
 *
 * build() returns:
 *   [
 *     'columns' => [ ['key'=>..., 'label'=>..., 'align'=>'left|right|center'], ... ],
 *     'rows'    => [ [colKey => value, ...], ... ],   // already display-formatted
 *     'chart'   => ['type'=>'bar|line', 'labels'=>[], 'datasets'=>[...]] | null,
 *     'kpis'    => [ ['label'=>..., 'value'=>...], ... ],
 *     'title'   => string,
 *   ]
 */
class Reports
{
    /** Report type registry: type => [label, orientation for PDF]. */
    public static function types(): array
    {
        return [
            'price'      => ['label' => 'สรุปราคากลางตามหมวด/ปี', 'orientation' => 'portrait'],
            'comparison' => ['label' => 'สรุปการเปรียบเทียบราคา',  'orientation' => 'landscape'],
            'spec'       => ['label' => 'สรุปคุณลักษณะ/TOR',       'orientation' => 'portrait'],
            'activity'   => ['label' => 'รายงานความเคลื่อนไหว',     'orientation' => 'portrait'],
        ];
    }

    public static function isValidType(string $type): bool
    {
        return array_key_exists($type, self::types());
    }

    public static function label(string $type): string
    {
        return self::types()[$type]['label'] ?? $type;
    }

    public static function orientation(string $type): string
    {
        return self::types()[$type]['orientation'] ?? 'portrait';
    }

    /**
     * @param array{year?:string,category?:string,month?:string} $filters
     */
    public static function build(string $type, array $filters = []): array
    {
        return match ($type) {
            'comparison' => self::comparison($filters),
            'spec'       => self::spec($filters),
            'activity'   => self::activity($filters),
            default      => self::price($filters),
        };
    }

    // ── price summary by category/year ──────────────────────────────────────
    private static function price(array $filters): array
    {
        $year     = $filters['year'] ?? 'all';
        $category = $filters['category'] ?? 'all';

        $products = Product::all()
            ->when($category !== 'all', fn ($c) => $c->filter(fn ($p) => $p->category === $category))
            ->when($year !== 'all', fn ($c) => $c->filter(fn ($p) => substr((string) $p->price_date, 0, 4) === $year));

        $byCat = [];
        foreach (Specs::categories() as $cat) {
            $byCat[$cat->slug] = ['label' => $cat->label, 'color' => $cat->color, 'count' => 0, 'min' => null, 'max' => 0, 'sum' => 0, 'priced' => 0];
        }
        foreach ($products as $p) {
            if (! isset($byCat[$p->category])) {
                continue;
            }
            $byCat[$p->category]['count']++;
            $price = (float) $p->price;
            if ($price > 0) {
                $byCat[$p->category]['min'] = is_null($byCat[$p->category]['min']) ? $price : min($byCat[$p->category]['min'], $price);
                $byCat[$p->category]['max'] = max($byCat[$p->category]['max'], $price);
                $byCat[$p->category]['sum'] += $price;
                $byCat[$p->category]['priced']++;
            }
        }

        $rows = [];
        $chartLabels = [];
        $chartData = [];
        $chartColors = [];
        foreach ($byCat as $stat) {
            if ($stat['count'] === 0) {
                continue;
            }
            $avg = $stat['priced'] ? $stat['sum'] / $stat['priced'] : 0;
            $rows[] = [
                'category' => $stat['label'],
                'count'    => number_format($stat['count']),
                'min'      => $stat['min'] ? number_format($stat['min']) : '-',
                'max'      => $stat['max'] ? number_format($stat['max']) : '-',
                'avg'      => $avg ? number_format($avg) : '-',
            ];
            $chartLabels[] = $stat['label'];
            $chartData[]   = round($avg);
            $chartColors[] = $stat['color'];
        }

        $priced = $products->filter(fn ($p) => (float) $p->price > 0);
        $avgAll = $priced->count() ? $priced->sum(fn ($p) => (float) $p->price) / $priced->count() : 0;

        return [
            'title'   => self::label('price'),
            'columns' => [
                ['key' => 'category', 'label' => 'หมวดหมู่', 'align' => 'left'],
                ['key' => 'count', 'label' => 'จำนวน', 'align' => 'right'],
                ['key' => 'min', 'label' => 'ราคาต่ำสุด (บาท)', 'align' => 'right'],
                ['key' => 'max', 'label' => 'ราคาสูงสุด (บาท)', 'align' => 'right'],
                ['key' => 'avg', 'label' => 'ราคาเฉลี่ย (บาท)', 'align' => 'right'],
            ],
            'rows'  => $rows,
            'chart' => $chartLabels ? [
                'type'   => 'bar',
                'labels' => $chartLabels,
                'datasets' => [[
                    'label'           => 'ราคาเฉลี่ย (บาท)',
                    'data'            => $chartData,
                    'backgroundColor' => $chartColors,
                    'borderRadius'    => 6,
                    'borderWidth'     => 0,
                ]],
            ] : null,
            'kpis' => [
                ['label' => 'จำนวนสินค้า', 'value' => number_format($products->count())],
                ['label' => 'ราคาเฉลี่ยรวม', 'value' => $avgAll ? number_format($avgAll) . ' ฿' : '-'],
                ['label' => 'ราคาสูงสุด', 'value' => $products->count() ? number_format($products->max(fn ($p) => (float) $p->price)) . ' ฿' : '-'],
                ['label' => 'หมวดที่มีข้อมูล', 'value' => (string) count($rows)],
            ],
        ];
    }

    // ── comparison summary ──────────────────────────────────────────────────
    private static function comparison(array $filters): array
    {
        $year     = $filters['year'] ?? 'all';
        $category = $filters['category'] ?? 'all';

        $all = Comparison::with('vendors')->get();
        $getYear = fn ($c) => $c->year ?: substr((string) ($c->created_date ?? ''), 0, 4) ?: '-';

        $filtered = $all
            ->when($category !== 'all', fn ($col) => $col->filter(fn ($c) => $c->category === $category))
            ->when($year !== 'all', fn ($col) => $col->filter(fn ($c) => $getYear($c) === $year))
            ->sortBy(fn ($c) => mb_strtolower($c->name ?? ''))
            ->values();

        $rows = [];
        $finalCount = 0;
        $minSum = 0;
        $minCount = 0;
        $catCounts = [];
        foreach ($filtered as $c) {
            $prices = $c->vendors->map(fn ($v) => (float) $v->price)->filter(fn ($p) => $p > 0);
            $min = $prices->min();
            $max = $prices->max();
            if ($c->status === 'final') {
                $finalCount++;
            }
            if ($min) {
                $minSum += $min;
                $minCount++;
            }
            $label = Specs::label($c->category);
            $catCounts[$label] = ($catCounts[$label] ?? 0) + 1;

            $rows[] = [
                'name'   => $c->name,
                'category' => $label,
                'period' => trim(($c->month ? Specs::monthLabel($c->month) . ' ' : '') . $getYear($c)),
                'min'    => $min ? number_format($min) : '-',
                'max'    => $max ? number_format($max) : '-',
                'diff'   => ($min && $max) ? number_format($max - $min) : '-',
                'status' => $c->status === 'final' ? 'อนุมัติแล้ว' : 'ร่าง',
            ];
        }

        return [
            'title'   => self::label('comparison'),
            'columns' => [
                ['key' => 'name', 'label' => 'ชื่อการเปรียบเทียบ', 'align' => 'left'],
                ['key' => 'category', 'label' => 'หมวด', 'align' => 'left'],
                ['key' => 'period', 'label' => 'ช่วงเวลา', 'align' => 'left'],
                ['key' => 'min', 'label' => 'ราคาต่ำสุด', 'align' => 'right'],
                ['key' => 'max', 'label' => 'ราคาสูงสุด', 'align' => 'right'],
                ['key' => 'diff', 'label' => 'ผลต่าง', 'align' => 'right'],
                ['key' => 'status', 'label' => 'สถานะ', 'align' => 'center'],
            ],
            'rows'  => $rows,
            'chart' => $catCounts ? [
                'type'   => 'bar',
                'labels' => array_keys($catCounts),
                'datasets' => [[
                    'label'           => 'จำนวนการเปรียบเทียบ',
                    'data'            => array_values($catCounts),
                    'backgroundColor' => '#2563EB',
                    'borderRadius'    => 6,
                    'borderWidth'     => 0,
                ]],
            ] : null,
            'kpis' => [
                ['label' => 'การเปรียบเทียบ', 'value' => number_format($filtered->count())],
                ['label' => 'อนุมัติแล้ว', 'value' => number_format($finalCount)],
                ['label' => 'ร่าง', 'value' => number_format($filtered->count() - $finalCount)],
                ['label' => 'ราคาต่ำสุดเฉลี่ย', 'value' => $minCount ? number_format($minSum / $minCount) . ' ฿' : '-'],
            ],
        ];
    }

    // ── spec / TOR summary ──────────────────────────────────────────────────
    private static function spec(array $filters): array
    {
        $year     = $filters['year'] ?? 'all';
        $category = $filters['category'] ?? 'all';

        $all = CharacteristicsTemplate::all();
        $getYear = fn ($s) => $s->year ?: substr((string) ($s->created_date ?? ''), 0, 4) ?: '-';

        $filtered = $all
            ->when($category !== 'all', fn ($col) => $col->filter(fn ($s) => $s->category === $category))
            ->when($year !== 'all', fn ($col) => $col->filter(fn ($s) => $getYear($s) === $year))
            ->sortBy(fn ($s) => mb_strtolower($s->name ?? ''))
            ->values();

        $rows = [];
        $budgetByCat = [];
        $budgetSum = 0;
        foreach ($filtered as $s) {
            $budget = (float) ($s->budget ?? 0);
            $budgetSum += $budget;
            $label = Specs::label($s->category);
            $budgetByCat[$label] = ($budgetByCat[$label] ?? 0) + $budget;

            $rows[] = [
                'name'     => $s->name,
                'category' => $label,
                'period'   => trim(($s->month ? Specs::monthLabel($s->month) . ' ' : '') . $getYear($s)),
                'budget'   => $budget ? number_format($budget) : '-',
                'items'    => (string) (is_array($s->specs) ? count($s->specs) : 0),
            ];
        }

        return [
            'title'   => self::label('spec'),
            'columns' => [
                ['key' => 'name', 'label' => 'ชื่อคุณลักษณะ / TOR', 'align' => 'left'],
                ['key' => 'category', 'label' => 'หมวด', 'align' => 'left'],
                ['key' => 'period', 'label' => 'ช่วงเวลา', 'align' => 'left'],
                ['key' => 'budget', 'label' => 'วงเงิน (บาท)', 'align' => 'right'],
                ['key' => 'items', 'label' => 'จำนวนข้อกำหนด', 'align' => 'right'],
            ],
            'rows'  => $rows,
            'chart' => $budgetByCat ? [
                'type'   => 'bar',
                'labels' => array_keys($budgetByCat),
                'datasets' => [[
                    'label'           => 'วงเงินรวม (บาท)',
                    'data'            => array_map(fn ($v) => round($v), array_values($budgetByCat)),
                    'backgroundColor' => '#059669',
                    'borderRadius'    => 6,
                    'borderWidth'     => 0,
                ]],
            ] : null,
            'kpis' => [
                ['label' => 'จำนวน TOR', 'value' => number_format($filtered->count())],
                ['label' => 'วงเงินรวม', 'value' => $budgetSum ? number_format($budgetSum) . ' ฿' : '-'],
                ['label' => 'วงเงินเฉลี่ย', 'value' => $filtered->count() ? number_format($budgetSum / $filtered->count()) . ' ฿' : '-'],
                ['label' => 'หมวดที่มีข้อมูล', 'value' => (string) count($budgetByCat)],
            ],
        ];
    }

    // ── activity / edit history ─────────────────────────────────────────────
    private static function activity(array $filters): array
    {
        $year = $filters['year'] ?? 'all';

        $prod = ProductEditHistory::orderByDesc('id')->get()->map(fn ($h) => [
            'date'   => (string) $h->date,
            'type'   => 'สินค้า',
            'user'   => $h->user,
            'action' => $h->action,
            'detail' => $h->detail,
        ]);
        $spec = CharacteristicsTemplateHistory::orderByDesc('id')->get()->map(fn ($h) => [
            'date'   => (string) $h->date,
            'type'   => 'คุณลักษณะ',
            'user'   => $h->user,
            'action' => $h->action,
            'detail' => $h->detail,
        ]);

        $merged = $prod->concat($spec)
            ->when($year !== 'all', fn ($c) => $c->filter(fn ($r) => substr((string) $r['date'], 0, 4) === $year))
            ->sortByDesc('date')
            ->values();

        $rows = $merged->take(300)->map(fn ($r) => [
            'date'   => $r['date'] ?: '-',
            'type'   => $r['type'],
            'user'   => $r['user'] ?: '-',
            'action' => $r['action'] ?: '-',
            'detail' => $r['detail'] ?: '-',
        ])->all();

        $prodCount = $merged->where('type', 'สินค้า')->count();
        $specCount = $merged->where('type', 'คุณลักษณะ')->count();

        return [
            'title'   => self::label('activity'),
            'columns' => [
                ['key' => 'date', 'label' => 'วันที่', 'align' => 'left'],
                ['key' => 'type', 'label' => 'ประเภท', 'align' => 'left'],
                ['key' => 'user', 'label' => 'ผู้ใช้', 'align' => 'left'],
                ['key' => 'action', 'label' => 'การกระทำ', 'align' => 'left'],
                ['key' => 'detail', 'label' => 'รายละเอียด', 'align' => 'left'],
            ],
            'rows'  => $rows,
            'chart' => $merged->count() ? [
                'type'   => 'bar',
                'labels' => ['สินค้า', 'คุณลักษณะ'],
                'datasets' => [[
                    'label'           => 'จำนวนรายการ',
                    'data'            => [$prodCount, $specCount],
                    'backgroundColor' => ['#2563EB', '#059669'],
                    'borderRadius'    => 6,
                    'borderWidth'     => 0,
                ]],
            ] : null,
            'kpis' => [
                ['label' => 'รายการทั้งหมด', 'value' => number_format($merged->count())],
                ['label' => 'แก้ไขสินค้า', 'value' => number_format($prodCount)],
                ['label' => 'แก้ไขคุณลักษณะ', 'value' => number_format($specCount)],
                ['label' => 'ผู้ใช้', 'value' => (string) $merged->pluck('user')->filter()->unique()->count()],
            ],
        ];
    }
}
