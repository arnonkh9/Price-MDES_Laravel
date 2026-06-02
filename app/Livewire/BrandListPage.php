<?php

namespace App\Livewire;

use App\Models\Brand;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('จัดการแบรนด์สินค้า | ระบบราคากลาง')]
class BrandListPage extends Component
{
    public ?int $editingId = null;
    public string $editName = '';
    public int $editPosition = 0;
    public string $newName = '';
    public int $newPosition = 0;

    public function mount(): void
    {
        abort_unless(auth()->user()->hasPermission('brands', 'view'), 403);
    }

    public function startEdit(int $id): void
    {
        abort_unless(auth()->user()->hasPermission('brands', 'edit'), 403);
        $brand = Brand::find($id);
        if (! $brand) {
            return;
        }
        $this->editingId = $id;
        $this->editName = $brand->name;
        $this->editPosition = $brand->position ?? 0;
        $this->resetValidation(['editName', 'editPosition']);
    }

    public function saveEdit(): void
    {
        abort_unless(auth()->user()->hasPermission('brands', 'edit'), 403);
        $this->validate([
            'editName' => 'required|string|max:100',
            'editPosition' => 'integer|min:0',
        ], [], [
            'editName' => 'ชื่อแบรนด์',
            'editPosition' => 'ลำดับที่',
        ]);

        Brand::where('id', $this->editingId)->update([
            'name' => trim($this->editName),
            'position' => $this->editPosition,
        ]);

        $this->editingId = null;
        $this->dispatch('toast', message: 'บันทึกสำเร็จ');
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->resetValidation(['editName', 'editPosition']);
    }

    public function addBrand(): void
    {
        abort_unless(auth()->user()->hasPermission('brands', 'add'), 403);
        $this->validate([
            'newName' => 'required|string|max:100',
            'newPosition' => 'integer|min:0',
        ], [], [
            'newName' => 'ชื่อแบรนด์',
            'newPosition' => 'ลำดับที่',
        ]);

        Brand::create([
            'name' => trim($this->newName),
            'position' => $this->newPosition > 0 ? $this->newPosition : (Brand::max('position') ?? 0) + 1,
        ]);

        $this->newName = '';
        $this->newPosition = 0;
        $this->dispatch('toast', message: 'เพิ่มแบรนด์สำเร็จ');
    }

    public function deleteBrand(int $id): void
    {
        abort_unless(auth()->user()->hasPermission('brands', 'delete'), 403);
        Brand::destroy($id);
        $this->dispatch('toast', message: 'ลบสำเร็จ');
    }

    public function render()
    {
        $user = auth()->user();
        return view('livewire.brand-list-page', [
            'brands'    => Brand::orderBy('position')->get(),
            'canAdd'    => $user->hasPermission('brands', 'add'),
            'canEdit'   => $user->hasPermission('brands', 'edit'),
            'canDelete' => $user->hasPermission('brands', 'delete'),
        ]);
    }
}
