<?php

namespace App\Livewire;

use App\Models\CharacteristicsTemplate;
use App\Models\Comparison;
use App\Models\GuidelineItem;
use App\Models\Product;
use App\Models\RecommendationItem;
use App\Support\Specs;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('ค้นหา | ระบบราคากลาง')]
class SearchPage extends Component
{
    #[Url(as: 'q')]
    public string $query = '';

    public function mount(): void
    {
        abort_unless(auth()->check(), 403);
    }

    public function render()
    {
        $q      = trim($this->query);
        $results = [];
        $total   = 0;

        if (strlen($q) >= 2) {
            $like = '%' . $q . '%';

            // ---- Products ----
            $products = Product::where(function ($w) use ($like) {
                $w->where('brand', 'ilike', $like)
                  ->orWhere('model', 'ilike', $like)
                  ->orWhere('category', 'ilike', $like)
                  ->orWhereRaw("specs::text ilike ?", [$like]);
            })->orderByDesc('updated_at')->limit(10)->get();

            if ($products->isNotEmpty()) {
                $colors = Specs::colorMap();
                $results['products'] = [
                    'label'   => 'สินค้า',
                    'icon'    => 'monitor',
                    'color'   => '#2563EB',
                    'route'   => 'products',
                    'items'   => $products->map(fn ($p) => [
                        'id'       => $p->id,
                        'title'    => $p->model,
                        'subtitle' => $p->brand . ' · ' . Specs::label($p->category),
                        'meta'     => $p->price ? number_format((float)$p->price) . ' ฿' : null,
                        'color'    => $colors[$p->category] ?? '#64748B',
                        'link'     => route('products', ['view' => $p->id]),
                    ])->toArray(),
                    'count'   => $products->count(),
                ];
                $total += $products->count();
            }

            // ---- Characteristics/Specs ----
            if (auth()->user()->canSeeMenu('specs')) {
                $specs = CharacteristicsTemplate::where(function ($w) use ($like) {
                    $w->where('name', 'ilike', $like)
                      ->orWhere('purpose', 'ilike', $like)
                      ->orWhere('created_by', 'ilike', $like)
                      ->orWhereRaw("specs::text ilike ?", [$like]);
                })->orderByDesc('updated_at')->limit(8)->get();

                if ($specs->isNotEmpty()) {
                    $results['specs'] = [
                        'label' => 'คุณลักษณะพื้นฐาน',
                        'icon'  => 'file',
                        'color' => '#7C3AED',
                        'route' => 'specs',
                        'items' => $specs->map(fn ($s) => [
                            'id'       => $s->id,
                            'title'    => $s->name,
                            'subtitle' => Specs::label($s->category) . ($s->year ? ' · ' . $s->year : ''),
                            'meta'     => $s->budget ? number_format((float)$s->budget) . ' ฿' : null,
                            'color'    => '#7C3AED',
                            'link'     => route('specs'),
                        ])->toArray(),
                        'count' => $specs->count(),
                    ];
                    $total += $specs->count();
                }
            }

            // ---- Comparisons ----
            if (auth()->user()->canSeeMenu('comparisons')) {
                $comparisons = Comparison::where(function ($w) use ($like) {
                    $w->where('name', 'ilike', $like)
                      ->orWhere('notes', 'ilike', $like)
                      ->orWhere('created_by', 'ilike', $like);
                })->orderByDesc('updated_at')->limit(8)->get();

                if ($comparisons->isNotEmpty()) {
                    $results['comparisons'] = [
                        'label' => 'การเปรียบเทียบ',
                        'icon'  => 'bar-chart',
                        'color' => '#059669',
                        'route' => 'comparisons',
                        'items' => $comparisons->map(fn ($c) => [
                            'id'       => $c->id,
                            'title'    => $c->name,
                            'subtitle' => Specs::label($c->category) . ($c->year ? ' · ' . $c->year : '') . ' · สร้างโดย ' . ($c->created_by ?: '-'),
                            'meta'     => $c->status === 'final' ? 'อนุมัติแล้ว' : 'ร่าง',
                            'color'    => '#059669',
                            'link'     => route('comparisons'),
                        ])->toArray(),
                        'count' => $comparisons->count(),
                    ];
                    $total += $comparisons->count();
                }
            }

            // ---- Guidelines ----
            if (auth()->user()->canSeeMenu('guidelines')) {
                $guidelines = GuidelineItem::where('content', 'ilike', $like)
                    ->limit(5)->get();

                if ($guidelines->isNotEmpty()) {
                    $results['guidelines'] = [
                        'label' => 'แนวทางการพิจารณา',
                        'icon'  => 'list',
                        'color' => '#0891B2',
                        'route' => 'guidelines',
                        'items' => $guidelines->map(fn ($g) => [
                            'id'       => $g->id,
                            'title'    => \Illuminate\Support\Str::limit($g->content, 80),
                            'subtitle' => null,
                            'meta'     => null,
                            'color'    => '#0891B2',
                            'link'     => route('guidelines'),
                        ])->toArray(),
                        'count' => $guidelines->count(),
                    ];
                    $total += $guidelines->count();
                }
            }

            // ---- Recommendations ----
            if (auth()->user()->canSeeMenu('recommendations')) {
                $recs = RecommendationItem::where('content', 'ilike', $like)
                    ->limit(5)->get();

                if ($recs->isNotEmpty()) {
                    $results['recommendations'] = [
                        'label' => 'ข้อแนะนำประกอบ',
                        'icon'  => 'check-circle',
                        'color' => '#D97706',
                        'route' => 'recommendations',
                        'items' => $recs->map(fn ($r) => [
                            'id'       => $r->id,
                            'title'    => \Illuminate\Support\Str::limit($r->content, 80),
                            'subtitle' => null,
                            'meta'     => null,
                            'color'    => '#D97706',
                            'link'     => route('recommendations'),
                        ])->toArray(),
                        'count' => $recs->count(),
                    ];
                    $total += $recs->count();
                }
            }
        }

        return view('livewire.search-page', [
            'results' => $results,
            'total'   => $total,
            'query'   => $q,
        ]);
    }
}
