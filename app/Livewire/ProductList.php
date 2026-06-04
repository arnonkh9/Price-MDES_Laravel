<?php

namespace App\Livewire;

use App\Models\Product;
use App\Support\CompareCart;
use App\Support\Specs;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('รายการสินค้า | ระบบราคากลาง')]
class ProductList extends Component
{
    #[Url]
    public string $category = 'all';

    #[Url]
    public string $search = '';

    public string $sortBy = 'brand';
    public string $sortDir = 'asc';
    public string $viewMode = 'table';

    #[Url]
    public string $filterBrand = '';

    #[Url]
    public string $filterYear = '';

    #[Url]
    public string $filterModel = '';

    public int $compareCount = 0;

    /** IDs ที่ผู้ใช้ tick เลือกไว้ (สำหรับ bulk-delete) */
    public array $selectedIds = [];

    /** IDs ของ products ที่แสดงอยู่ปัจจุบัน (อัปเดตทุก render) */
    public array $currentProductIds = [];

    public function mount()
    {
        $this->compareCount = CompareCart::count();

        // Deep-link actions from header / sidebar / dashboard.
        if (request('action') === 'new' && auth()->user()->hasPermission('products', 'add')) {
            $this->dispatch('open-product-form');
        }
        if (request('action') === 'import' && auth()->user()->hasPermission('products', 'import')) {
            $this->dispatch('open-import');
        }
        if (request('action') === 'categories' && auth()->user()->hasPermission('categories', 'view')) {
            $this->dispatch('open-categories');
        }
        if (request('action') === 'brands' && auth()->user()->hasPermission('brands', 'view')) {
            $this->dispatch('open-brands');
        }
        if (request('view')) {
            $this->dispatch('open-product-detail', id: request('view'));
        }
    }

    public function resetFilters(): void
    {
        $this->category    = 'all';
        $this->filterBrand = '';
        $this->filterYear  = '';
        $this->filterModel = '';
        $this->search      = '';
    }

    public function sortPriceDir(string $dir): void
    {
        $this->sortBy  = 'price';
        $this->sortDir = in_array($dir, ['asc', 'desc']) ? $dir : 'asc';
    }

    public function sort(string $field)
    {
        if ($this->sortBy === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDir = 'asc';
        }
    }

    public function toggleSelectAll(): void
    {
        // Match the $allSelected logic in render() (diff-based, not count-based)
        // so the header checkbox state and this toggle stay consistent even when
        // selectedIds contains items outside the current filter.
        $allSelected = ! empty($this->currentProductIds)
            && empty(array_diff($this->currentProductIds, $this->selectedIds));
        $this->selectedIds = $allSelected ? [] : $this->currentProductIds;
    }

    public function bulkDelete(): void
    {
        abort_unless(auth()->user()->hasPermission('products', 'delete'), 403);
        $count = count($this->selectedIds);
        if ($count === 0) return;

        Product::whereIn('id', $this->selectedIds)->delete();
        foreach ($this->selectedIds as $id) {
            CompareCart::remove($id);
        }
        $this->compareCount  = CompareCart::count();
        $this->selectedIds   = [];
        $this->dispatch('toast', message: "ลบสินค้าสำเร็จ {$count} รายการ");
        $this->dispatch('cart-updated');
    }

    public function toggleCompare(string $id)
    {
        $result = CompareCart::toggle($id);
        $this->compareCount = CompareCart::count();
        if ($result === 'full') {
            $this->dispatch('toast', message: 'เปรียบเทียบได้สูงสุด 3 รายการ', type: 'warn');
        }
        $this->dispatch('cart-updated');
    }

    public function deleteProduct(string $id)
    {
        abort_unless(auth()->user()->hasPermission('products', 'delete'), 403);
        Product::destroy($id);
        CompareCart::remove($id);
        $this->compareCount = CompareCart::count();
        $this->dispatch('toast', message: 'ลบสินค้าสำเร็จ');
        $this->dispatch('cart-updated');
    }

    #[\Livewire\Attributes\On('product-saved')]
    #[\Livewire\Attributes\On('cart-updated')]
    public function refreshCount()
    {
        $this->compareCount = CompareCart::count();
    }

    public function render()
    {
        $sortable = ['brand', 'model', 'category', 'price'];
        $sortBy = in_array($this->sortBy, $sortable) ? $this->sortBy : 'brand';

        // Base query scoped to the selected category (used for filter option lists)
        $baseQuery = Product::query()
            ->when($this->category !== 'all', fn ($q) => $q->where('category', $this->category));

        // Distinct brands and years available within the selected category
        $availableBrands = (clone $baseQuery)
            ->distinct()->orderBy('brand')->pluck('brand');

        $availableYears = (clone $baseQuery)
            ->whereNotNull('price_date')->where('price_date', '!=', '')
            ->distinct()->orderByDesc('price_date')->pluck('price_date')
            ->map(fn ($d) => substr($d, 0, 4))->unique()->values();

        // Apply additional filters on top of base query
        $query = clone $baseQuery;
        if ($this->filterBrand !== '') $query->where('brand', $this->filterBrand);
        if ($this->filterYear  !== '') $query->where('price_date', 'like', $this->filterYear.'%');
        if ($this->filterModel !== '') $query->where('model', 'like', '%'.$this->filterModel.'%');

        $products = $query->get();

        // Full-text search (in-memory; also covers specs JSON fields)
        if ($this->search !== '') {
            $q = mb_strtolower($this->search);
            $products = $products->filter(function ($p) use ($q) {
                if (str_contains(mb_strtolower($p->brand), $q)) return true;
                if (str_contains(mb_strtolower($p->model), $q)) return true;
                if (str_contains(mb_strtolower($p->category), $q)) return true;
                foreach (($p->specs ?? []) as $v) {
                    if ($v && str_contains(mb_strtolower((string) $v), $q)) return true;
                }
                return false;
            });
        }

        $products = $products->sortBy(function ($p) use ($sortBy) {
            return $sortBy === 'price' ? (float) $p->price : mb_strtolower((string) $p->{$sortBy});
        }, SORT_REGULAR, $this->sortDir === 'desc')->values();

        // Track current visible IDs for select-all / bulk-delete
        $this->currentProductIds = $products->pluck('id')->all();

        $user = auth()->user();
        return view('livewire.product-list', [
            'products'        => $products,
            'colors'          => Specs::colorMap(),
            'catLabel'        => $this->category === 'all' ? 'ทั้งหมด' : Specs::label($this->category),
            'compareIds'      => CompareCart::ids(),
            'categories'      => Specs::categories(),
            'availableBrands' => $availableBrands,
            'availableYears'  => $availableYears,
            'hasFilters'      => $this->category !== 'all' || $this->filterBrand !== ''
                                 || $this->filterYear !== '' || $this->filterModel !== ''
                                 || $this->search !== '',
            'allSelected'     => count($this->currentProductIds) > 0
                                  && empty(array_diff($this->currentProductIds, $this->selectedIds)),
            'canAdd'          => $user->hasPermission('products', 'add'),
            'canEdit'         => $user->hasPermission('products', 'edit'),
            'canDelete'       => $user->hasPermission('products', 'delete'),
            'canImport'       => $user->hasPermission('products', 'import'),
            'canExport'       => $user->hasPermission('products', 'export'),
        ]);
    }
}
