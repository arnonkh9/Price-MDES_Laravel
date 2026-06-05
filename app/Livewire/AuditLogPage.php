<?php

namespace App\Livewire;

use App\Models\CharacteristicsTemplateHistory;
use App\Models\ProductEditHistory;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('ประวัติการแก้ไข | ระบบราคากลาง')]
class AuditLogPage extends Component
{
    use WithPagination;

    #[Url(as: 'type')]
    public string $filterType = '';   // '' = ทั้งหมด | 'product' | 'spec'

    #[Url(as: 'action')]
    public string $filterAction = ''; // '' = ทั้งหมด | 'add' | 'edit' | 'delete'

    #[Url(as: 'user')]
    public string $filterUser = '';

    #[Url(as: 'from')]
    public string $filterFrom = '';

    #[Url(as: 'to')]
    public string $filterTo = '';

    /** composite keys "type:id" ของแถวที่เลือก (สำหรับลบหลายรายการ) */
    public array $selectedKeys = [];

    /** composite keys ของแถวในหน้าปัจจุบัน (ใช้กับ select-all) */
    public array $currentKeys = [];

    public function mount(): void
    {
        abort_unless(auth()->check(), 403);
    }

    public function updatedFilterType(): void   { $this->resetPage(); }
    public function updatedFilterAction(): void { $this->resetPage(); }
    public function updatedFilterUser(): void   { $this->resetPage(); }
    public function updatedFilterFrom(): void   { $this->resetPage(); }
    public function updatedFilterTo(): void     { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->filterType   = '';
        $this->filterAction = '';
        $this->filterUser   = '';
        $this->filterFrom   = '';
        $this->filterTo     = '';
        $this->selectedKeys = [];
        $this->resetPage();
    }

    /** ลบประวัติ 1 รายการ (admin เท่านั้น — เป็น audit trail) */
    public function deleteLog(string $type, int $id): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        if ($type === 'product') {
            ProductEditHistory::whereKey($id)->delete();
        } elseif ($type === 'spec') {
            CharacteristicsTemplateHistory::whereKey($id)->delete();
        }

        $this->selectedKeys = array_values(array_diff($this->selectedKeys, ["{$type}:{$id}"]));
        $this->dispatch('toast', message: 'ลบประวัติสำเร็จ');
    }

    public function toggleSelectItem(string $key): void
    {
        if (in_array($key, $this->selectedKeys)) {
            $this->selectedKeys = array_values(array_diff($this->selectedKeys, [$key]));
        } else {
            $this->selectedKeys[] = $key;
        }
    }

    public function toggleSelectAll(): void
    {
        // diff-based: ถ้าแถวในหน้านี้ถูกเลือกครบแล้ว → ล้าง, ไม่งั้น → เลือกทั้งหน้า
        $allSelected = ! empty($this->currentKeys)
            && empty(array_diff($this->currentKeys, $this->selectedKeys));
        $this->selectedKeys = $allSelected ? [] : $this->currentKeys;
    }

    /** ลบประวัติหลายรายการพร้อมกัน (admin เท่านั้น) */
    public function bulkDelete(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $productIds = [];
        $specIds    = [];
        foreach ($this->selectedKeys as $key) {
            [$type, $id] = array_pad(explode(':', $key, 2), 2, null);
            if ($type === 'product') {
                $productIds[] = (int) $id;
            } elseif ($type === 'spec') {
                $specIds[] = (int) $id;
            }
        }

        $count = count($productIds) + count($specIds);
        if ($count === 0) {
            return;
        }

        if ($productIds) {
            ProductEditHistory::whereIn('id', $productIds)->delete();
        }
        if ($specIds) {
            CharacteristicsTemplateHistory::whereIn('id', $specIds)->delete();
        }

        $this->selectedKeys = [];
        $this->resetPage();
        $this->dispatch('toast', message: "ลบประวัติสำเร็จ {$count} รายการ");
    }

    public function render()
    {
        $isAdmin = auth()->user()->isAdmin();
        $authUser = auth()->user()->name ?? auth()->user()->username;

        // Build product histories query
        $prodQuery = ProductEditHistory::query()
            ->when(! $isAdmin, fn ($q) => $q->where('user', $authUser))
            ->when($this->filterAction, fn ($q) => $q->where('action', $this->filterAction))
            ->when($this->filterUser && $isAdmin, fn ($q) => $q->where('user', 'ilike', '%' . $this->filterUser . '%'))
            ->when($this->filterFrom, fn ($q) => $q->where('date', '>=', $this->filterFrom))
            ->when($this->filterTo,   fn ($q) => $q->where('date', '<=', $this->filterTo))
            ->select(['id', 'product_id as ref_id', 'date', 'user', 'action', 'detail', 'source', 'url', 'created_at'])
            ->selectRaw("'product' as type");

        // Build spec histories query
        $specQuery = CharacteristicsTemplateHistory::query()
            ->when(! $isAdmin, fn ($q) => $q->where('user', $authUser))
            ->when($this->filterAction, fn ($q) => $q->where('action', $this->filterAction))
            ->when($this->filterUser && $isAdmin, fn ($q) => $q->where('user', 'ilike', '%' . $this->filterUser . '%'))
            ->when($this->filterFrom, fn ($q) => $q->where('date', '>=', $this->filterFrom))
            ->when($this->filterTo,   fn ($q) => $q->where('date', '<=', $this->filterTo))
            ->select(['id', 'characteristics_template_id as ref_id', 'date', 'user', 'action', 'detail'])
            ->selectRaw("NULL as source, NULL as url, created_at")
            ->selectRaw("'spec' as type");

        // Combine based on type filter
        if ($this->filterType === 'product') {
            $logs = $prodQuery->orderByDesc('created_at')->paginate(20);
        } elseif ($this->filterType === 'spec') {
            $logs = $specQuery->orderByDesc('created_at')->paginate(20);
        } else {
            // Union both — PostgreSQL union with paginate via manual subquery approach
            $union = $prodQuery->unionAll($specQuery);
            $logs = \Illuminate\Support\Facades\DB::table(\Illuminate\Support\Facades\DB::raw("({$union->toSql()}) as combined"))
                ->mergeBindings($union->getQuery())
                ->orderByDesc('created_at')
                ->paginate(20);
        }

        // Composite keys ของแถวในหน้าปัจจุบัน (สำหรับ select-all)
        $this->currentKeys = collect($logs->items())
            ->map(fn ($r) => $r->type . ':' . $r->id)
            ->all();
        $allSelected = count($this->currentKeys) > 0
            && empty(array_diff($this->currentKeys, $this->selectedKeys));

        return view('livewire.audit-log-page', [
            'logs'        => $logs,
            'isAdmin'     => $isAdmin,
            'allSelected' => $allSelected,
        ]);
    }
}
