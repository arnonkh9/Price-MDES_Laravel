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
        $this->resetPage();
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

        return view('livewire.audit-log-page', [
            'logs'    => $logs,
            'isAdmin' => $isAdmin,
        ]);
    }
}
