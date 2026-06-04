<?php

namespace App\Livewire;

use App\Models\Comparison;
use App\Models\CharacteristicsTemplate;
use App\Support\Specs;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('เทียบราคา 3 เจ้า | ระบบราคากลาง')]
class ComparisonList extends Component
{
    public string $selYear  = 'all';
    public string $selMonth = 'all';
    public string $sortBy   = 'name';
    public string $sortDir  = 'asc';
    public string $viewMode = 'table';
    public array $selectedIds = [];
    public array $currentComparisonIds = [];

    public function mount()
    {
        abort_unless(auth()->user()->canSeeMenu('comparisons'), 403);
        if (request('action') === 'new' && auth()->user()->hasPermission('comparisons', 'add')) {
            $this->dispatch('open-comparison-form');
        }
    }

    public function selectYear(string $y)
    {
        $this->selYear = $y;
        $this->selMonth = 'all';
    }

    public function sort(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy  = $field;
            $this->sortDir = 'asc';
        }
    }

    public function toggleSelectAll(): void
    {
        // Match the $allSelected logic in render() (diff-based, not count-based)
        // so the header checkbox state and this toggle stay consistent even when
        // selectedIds contains items outside the current filter.
        $allSelected = ! empty($this->currentComparisonIds)
            && empty(array_diff($this->currentComparisonIds, $this->selectedIds));
        $this->selectedIds = $allSelected ? [] : $this->currentComparisonIds;
    }

    public function toggleSelectItem(string $id): void
    {
        if (in_array($id, $this->selectedIds)) {
            $this->selectedIds = array_values(array_diff($this->selectedIds, [$id]));
        } else {
            $this->selectedIds[] = $id;
        }
    }

    #[On('comparison-saved')]
    public function refreshList(): void
    {
        // re-render เพื่อดึง comparison ล่าสุดหลังบันทึกจากฟอร์ม (Livewire re-render เมื่อ method ถูกเรียก)
    }

    public function deleteComparison(string $id)
    {
        abort_unless(auth()->user()->hasPermission('comparisons', 'delete'), 403);
        Comparison::destroy($id);
        $this->dispatch('toast', message: 'ลบสำเร็จ');
    }

    public function bulkExportComparisons()
    {
        abort_unless(auth()->user()->hasPermission('comparisons', 'export'), 403);
        if (empty($this->selectedIds)) {
            return;
        }
        return redirect()->route('comparisons.export.bulk', ['ids' => implode(',', $this->selectedIds)]);
    }

    public function render()
    {
        $all = Comparison::with('vendors')->get();
        $year = fn ($c) => $c->year ?: substr($c->created_date ?? '', 0, 4) ?: '2569';
        $month = fn ($c) => $c->month ?: '';

        $availYears = $all->map($year)->unique()->sort()->reverse()->values();
        $availMonths = ($this->selYear === 'all' ? $all : $all->filter(fn ($c) => $year($c) === $this->selYear))
            ->map($month)->filter()->unique()->sort()->values();

        $filtered = $all->filter(function ($c) use ($year, $month) {
            if ($this->selYear !== 'all' && $year($c) !== $this->selYear) {
                return false;
            }
            if ($this->selMonth !== 'all' && $month($c) !== $this->selMonth) {
                return false;
            }
            return true;
        })->sortBy(
            fn ($c) => match ($this->sortBy) {
                'name'      => mb_strtolower($c->name ?? ''),
                'category'  => $c->category ?? '',
                'status'    => $c->status ?? '',
                'year'      => $year($c) . ($month($c) ?: '00'),
                'min_price' => $c->vendors->map(fn ($v) => (float) $v->price)->filter(fn ($p) => $p > 0)->min() ?? PHP_FLOAT_MAX,
                default     => mb_strtolower($c->name ?? ''),
            },
            SORT_REGULAR,
            $this->sortDir === 'desc'
        )->values();

        $yearCounts = $availYears->mapWithKeys(fn ($y) => [$y => $all->filter(fn ($c) => $year($c) === $y)->count()]);
        $periodLabel = $this->selYear === 'all' ? 'ทุกช่วงเวลา'
            : ($this->selMonth === 'all' ? "ปี {$this->selYear}" : Specs::monthLabel($this->selMonth)." {$this->selYear}");

        $this->currentComparisonIds = $filtered->pluck('id')->all();
        $allSelected = count($this->currentComparisonIds) > 0
            && empty(array_diff($this->currentComparisonIds, $this->selectedIds));

        $user = auth()->user();
        return view('livewire.comparison-list', [
            'comparisons' => $filtered,
            'all' => $all,
            'colors' => Specs::colorMap(),
            'specs' => CharacteristicsTemplate::all()->keyBy('id'),
            'availYears' => $availYears,
            'availMonths' => $availMonths,
            'yearCounts' => $yearCounts,
            'periodLabel' => $periodLabel,
            'getYear' => $year,
            'getMonth' => $month,
            'allSelected' => $allSelected,
            'canAdd'    => $user->hasPermission('comparisons', 'add'),
            'canEdit'   => $user->hasPermission('comparisons', 'edit'),
            'canDelete' => $user->hasPermission('comparisons', 'delete'),
            'canExport' => $user->hasPermission('comparisons', 'export'),
        ]);
    }
}
