<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\ProductEditHistory;
use App\Models\CharacteristicsTemplateHistory;
use App\Support\Specs;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('แดชบอร์ด | ระบบราคากลาง')]
class Dashboard extends Component
{
    public function render()
    {
        $products  = Product::all();
        $categories = Specs::categories();
        $colors    = Specs::colorMap();

        // ---- per-category stats ----
        $byCategory = [];
        foreach ($categories as $cat) {
            $byCategory[$cat->slug] = ['count' => 0, 'min' => null, 'max' => 0, 'label' => $cat->label, 'color' => $cat->color];
        }
        foreach ($products as $p) {
            if (isset($byCategory[$p->category])) {
                $byCategory[$p->category]['count']++;
                $price = (float) $p->price;
                if ($price > 0) {
                    $byCategory[$p->category]['min'] = is_null($byCategory[$p->category]['min'])
                        ? $price : min($byCategory[$p->category]['min'], $price);
                    $byCategory[$p->category]['max'] = max($byCategory[$p->category]['max'], $price);
                }
            }
        }

        // ---- KPIs ----
        $priced   = $products->filter(fn ($p) => (float) $p->price > 0);
        $avg      = $priced->count() ? $priced->sum(fn ($p) => (float) $p->price) / $priced->count() : 0;
        $maxPrice = $products->max(fn ($p) => (float) $p->price) ?: 0;
        $catCount = collect($byCategory)->filter(fn ($s) => $s['count'] > 0)->count();
        $editCount = ProductEditHistory::count();

        // ---- recent products ----
        $recent = $products->sortByDesc(fn ($p) => optional($p->histories->last())->date ?? '')
            ->take(6)->values();

        // ---- Bar chart: products per category ----
        $nonEmpty = collect($byCategory)->filter(fn ($s) => $s['count'] > 0);
        $barChartData = [
            'labels'   => $nonEmpty->pluck('label')->values()->toArray(),
            'datasets' => [[
                'data'            => $nonEmpty->pluck('count')->values()->toArray(),
                'backgroundColor' => $nonEmpty->pluck('color')->values()->toArray(),
                'borderRadius'    => 6,
                'borderWidth'     => 0,
            ]],
        ];

        // ---- Line chart: avg price by year (from price_date 'YYYY-MM-DD') ----
        $trend = $products
            ->filter(fn ($p) => (float) $p->price > 0 && strlen((string) $p->price_date) >= 4)
            ->groupBy(fn ($p) => substr($p->price_date, 0, 4))
            ->sortKeys()
            ->map(fn ($group) => round($group->avg(fn ($p) => (float) $p->price)))
            ->toArray();

        $trendChartData = [
            'labels'   => array_keys($trend),
            'datasets' => [[
                'label'           => 'ราคากลางเฉลี่ย (บาท)',
                'data'            => array_values($trend),
                'borderColor'     => '#2563EB',
                'backgroundColor' => '#2563EB22',
                'fill'            => true,
                'tension'         => 0.4,
                'pointRadius'     => 5,
                'pointBackgroundColor' => '#2563EB',
            ]],
        ];

        // ---- Activity feed: latest edits (products + specs) ----
        $prodActivities = ProductEditHistory::orderByDesc('id')->take(6)->get()
            ->map(fn ($h) => [
                'type'   => 'product',
                'date'   => $h->date,
                'user'   => $h->user,
                'action' => $h->action,
                'detail' => $h->detail,
            ]);

        $specActivities = CharacteristicsTemplateHistory::orderByDesc('id')->take(4)->get()
            ->map(fn ($h) => [
                'type'   => 'spec',
                'date'   => $h->date,
                'user'   => $h->user,
                'action' => $h->action,
                'detail' => $h->detail,
            ]);

        $activities = $prodActivities->concat($specActivities)
            ->sortByDesc('date')
            ->take(8)
            ->values();

        return view('livewire.dashboard', [
            'products'       => $products,
            'categories'     => $categories,
            'colors'         => $colors,
            'stats'          => $byCategory,
            'total'          => $products->count(),
            'avg'            => $avg,
            'maxPrice'       => $maxPrice,
            'catCount'       => $catCount,
            'editCount'      => $editCount,
            'recent'         => $recent,
            'barChartData'   => $barChartData,
            'trendChartData' => $trendChartData,
            'activities'     => $activities,
        ]);
    }
}
