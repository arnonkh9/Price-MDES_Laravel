<?php

namespace App\Livewire;

use App\Models\CharacteristicsTemplate;
use App\Models\CharacteristicsTemplateHistory;
use App\Support\CompareCart;
use App\Support\Specs;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('คุณลักษณะพื้นฐาน | ระบบราคากลาง')]
class CharacteristicsList extends Component
{
    public string $selYear       = 'all';
    public string $selMonth      = 'all';
    public array  $selCategories = [];        // [] = all, ['Notebook','Server'] = only those
    public string $search        = '';        // text search on name + purpose
    public string $sortBy        = 'name';
    public string $sortDir       = 'asc';
    public string $viewMode      = 'table';
    public array  $selectedIds   = [];
    public array  $currentCharacteristicIds = [];

    public function sort(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy  = $field;
            $this->sortDir = 'asc';
        }
    }

    public function mount()
    {
        abort_unless(auth()->user()->canSeeMenu('specs'), 403);
        if (request('action') === 'new' && auth()->user()->hasPermission('specs', 'add')) {
            $this->dispatch('open-characteristics-form');
        }
    }

    public function selectYear(string $y): void
    {
        $this->selYear = $y;
        // NOTE: month is now independent — do NOT reset selMonth here
        $this->selectedIds = [];
    }

    public function toggleCategory(string $cat): void
    {
        if (in_array($cat, $this->selCategories)) {
            $this->selCategories = array_values(array_diff($this->selCategories, [$cat]));
        } else {
            $this->selCategories[] = $cat;
        }
        $this->selectedIds = [];
    }

    public function clearFilters(): void
    {
        $this->selYear       = 'all';
        $this->selMonth      = 'all';
        $this->selCategories = [];
        $this->search        = '';
        $this->selectedIds   = [];
    }

    public function updatedSelMonth(): void
    {
        $this->selectedIds = [];
    }

    public function updatedSearch(): void
    {
        $this->selectedIds = [];
    }

    public function useCompare(string $id)
    {
        CompareCart::setBaseSpec($id);
        $this->dispatch('toast', message: 'ตั้งเป็นสเปคอ้างอิงแล้ว');
        $this->redirect(route('compare'), navigate: true);
    }

    public function deleteCharacteristics(string $id)
    {
        abort_unless(auth()->user()->hasPermission('specs', 'delete'), 403);
        CharacteristicsTemplate::destroy($id);
        if (CompareCart::baseSpecId() === $id) {
            CompareCart::setBaseSpec(null);
        }
        $this->dispatch('toast', message: 'ลบคุณลักษณะพื้นฐานสำเร็จ');
    }

    public function toggleSelectAll(): void
    {
        // Match the $allSelected logic in render() (diff-based, not count-based)
        // so the header checkbox state and this toggle stay consistent even when
        // selectedIds contains items outside the current filter.
        $allSelected = ! empty($this->currentCharacteristicIds)
            && empty(array_diff($this->currentCharacteristicIds, $this->selectedIds));
        $this->selectedIds = $allSelected ? [] : $this->currentCharacteristicIds;
    }

    public function toggleSelectItem(string $id): void
    {
        if (in_array($id, $this->selectedIds)) {
            $this->selectedIds = array_values(array_diff($this->selectedIds, [$id]));
        } else {
            $this->selectedIds[] = $id;
        }
    }

    public function bulkExportCharacteristics()
    {
        abort_unless(auth()->user()->hasPermission('specs', 'export'), 403);
        if (empty($this->selectedIds)) return;

        return redirect()->route('specs.export.bulk', ['ids' => implode(',', $this->selectedIds)]);
    }

    public function bulkDeleteCharacteristics(): void
    {
        abort_unless(auth()->user()->hasPermission('specs', 'delete'), 403);
        $count = count($this->selectedIds);
        if ($count === 0) return;

        CharacteristicsTemplate::whereIn('id', $this->selectedIds)->delete();

        // Delete associated history records
        foreach ($this->selectedIds as $id) {
            CharacteristicsTemplateHistory::where('characteristics_template_id', $id)->delete();
        }

        $this->selectedIds = [];
        $this->dispatch('toast', message: "ลบคุณลักษณะพื้นฐานสำเร็จ {$count} รายการ");
        $this->dispatch('characteristics-saved');
    }

    #[On('specs-imported')]
    #[On('characteristics-saved')]
    public function refreshList(): void
    {
        // Component will automatically re-render and fetch updated data
    }

    public function render()
    {
        $all   = CharacteristicsTemplate::all();
        $year  = fn ($s) => $s->year ?: substr($s->created_date ?? '', 0, 4) ?: '2569';
        $month = fn ($s) => $s->month ?: substr($s->created_date ?? '', 5, 2);

        $availYears = $all->map($year)->unique()->sort()->reverse()->values();

        // Available months across all data (or filtered by selected year)
        $monthBase   = $this->selYear === 'all' ? $all : $all->filter(fn ($s) => $year($s) === $this->selYear);
        $availMonths = $monthBase->map($month)->filter()->unique()->sort()->values();

        // Available categories (from all data — always show full list)
        $availCategories = $all->pluck('category')->filter()->unique()->sort()->values();

        // ── Multi-dimension filter ─────────────────────────────────────────
        $filtered = $all->filter(function ($s) use ($year, $month) {
            // Year
            if ($this->selYear !== 'all' && $year($s) !== $this->selYear) {
                return false;
            }
            // Month — now independent (works even when selYear = 'all')
            if ($this->selMonth !== 'all' && $month($s) !== $this->selMonth) {
                return false;
            }
            // Categories multi-select — if array non-empty, must match one
            if (!empty($this->selCategories) && !in_array($s->category, $this->selCategories)) {
                return false;
            }
            // Text search (name + purpose, case-insensitive)
            if ($this->search !== '') {
                $q        = mb_strtolower($this->search);
                $haystack = mb_strtolower(($s->name ?? '') . ' ' . ($s->purpose ?? ''));
                if (!str_contains($haystack, $q)) {
                    return false;
                }
            }
            return true;
        })->sortBy(
            fn ($s) => match ($this->sortBy) {
                'name'     => mb_strtolower($s->name ?? ''),
                'category' => $s->category ?? '',
                'year'     => $year($s),
                'budget'   => (float) ($s->budget ?? 0),
                default    => mb_strtolower($s->name ?? ''),
            },
            SORT_REGULAR,
            $this->sortDir === 'desc'
        )->values();

        $yearCounts = $availYears->mapWithKeys(fn ($y) => [$y => $all->filter(fn ($s) => $year($s) === $y)->count()]);

        $periodLabel = $this->selYear === 'all' ? 'ทุกช่วงเวลา'
            : ($this->selMonth === 'all' ? "ปี {$this->selYear}" : Specs::monthLabel($this->selMonth)." {$this->selYear}");

        $hasActiveFilters = $this->selYear !== 'all'
            || $this->selMonth !== 'all'
            || !empty($this->selCategories)
            || $this->search !== '';

        $this->currentCharacteristicIds = $filtered->pluck('id')->all();
        $allSelected = count($this->currentCharacteristicIds) > 0
            && empty(array_diff($this->currentCharacteristicIds, $this->selectedIds));

        $user = auth()->user();
        return view('livewire.characteristics-list', [
            'specs'           => $filtered,
            'all'             => $all,
            'colors'          => Specs::colorMap(),
            'availYears'      => $availYears,
            'availMonths'     => $availMonths,
            'availCategories' => $availCategories,
            'yearCounts'      => $yearCounts,
            'periodLabel'     => $periodLabel,
            'hasActiveFilters'=> $hasActiveFilters,
            'getYear'         => $year,
            'getMonth'        => $month,
            'allSelected'     => $allSelected,
            'canAdd'          => $user->hasPermission('specs', 'add'),
            'canEdit'         => $user->hasPermission('specs', 'edit'),
            'canDelete'       => $user->hasPermission('specs', 'delete'),
            'canImport'       => $user->hasPermission('specs', 'import'),
            'canExport'       => $user->hasPermission('specs', 'export'),
        ]);
    }
}
