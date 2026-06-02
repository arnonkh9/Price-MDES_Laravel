<?php

namespace App\Livewire;

use App\Models\Brand;
use App\Models\Product;
use Livewire\Attributes\On;
use Livewire\Component;

class BrandManager extends Component
{
    public bool $show = false;

    public string $newName = '';

    public ?int $editingId = null;
    public string $editName = '';

    #[On('open-brands')]
    public function open(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $this->reset(['newName']);
        $this->editingId = null;
        $this->show = true;
    }

    public function close(): void
    {
        $this->show = false;
    }

    public function startEdit(int $id): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $brand = Brand::find($id);
        if (! $brand) {
            return;
        }
        $this->editingId = $id;
        $this->editName = $brand->name;
        $this->resetValidation(['editName']);
    }

    public function saveEdit(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $this->validate([
            'editName' => 'required|string|unique:brands,name,'.$this->editingId,
        ], [], ['editName' => 'ชื่อแบรนด์']);

        Brand::where('id', $this->editingId)->update(['name' => trim($this->editName)]);
        $this->editingId = null;
        $this->dispatch('toast', message: 'อัปเดตแบรนด์สำเร็จ');
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->resetValidation(['editName']);
    }

    public function addBrand(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $this->validate([
            'newName' => 'required|string|unique:brands,name',
        ], [], ['newName' => 'ชื่อแบรนด์']);

        Brand::create([
            'name' => trim($this->newName),
            'position' => (Brand::max('position') ?? 0) + 1,
        ]);

        $this->reset(['newName']);
        $this->dispatch('toast', message: 'เพิ่มแบรนด์สำเร็จ');
    }

    public function deleteBrand(int $id): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $count = Product::where('brand', Brand::find($id)?->name ?? '')->count();
        if ($count > 0) {
            $this->dispatch('toast', message: "ไม่สามารถลบได้ มีสินค้า {$count} รายการที่ใช้แบรนด์นี้", type: 'warn');

            return;
        }
        Brand::destroy($id);
        $this->dispatch('toast', message: 'ลบแบรนด์สำเร็จ');
    }

    public function render()
    {
        return view('livewire.brand-manager', [
            'brands' => Brand::orderBy('name')->get(),
        ]);
    }
}
