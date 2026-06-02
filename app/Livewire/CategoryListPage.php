<?php

namespace App\Livewire;

use App\Models\Category;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('จัดการหมวดหมู่สินค้า | ระบบราคากลาง')]
class CategoryListPage extends Component
{
    public ?string $editingId = null;
    public string $editLabel = '';
    public string $editShort = '';
    public string $editColor = '';
    public int $editPosition = 0;
    public string $newLabel = '';
    public string $newShort = '';
    public string $newColor = '#000000';
    public int $newPosition = 0;

    public function mount(): void
    {
        abort_unless(auth()->user()->hasPermission('categories', 'view'), 403);
    }

    public function startEdit(string $slug): void
    {
        abort_unless(auth()->user()->hasPermission('categories', 'edit'), 403);
        $category = Category::where('slug', $slug)->first();
        if (! $category) {
            return;
        }
        $this->editingId = $slug;
        $this->editLabel = $category->label;
        $this->editShort = $category->short;
        $this->editColor = $category->color;
        $this->editPosition = $category->position ?? 0;
        $this->resetValidation(['editLabel', 'editShort', 'editColor', 'editPosition']);
    }

    public function saveEdit(): void
    {
        abort_unless(auth()->user()->hasPermission('categories', 'edit'), 403);
        $this->validate([
            'editLabel' => 'required|string|max:100',
            'editShort' => 'required|string|max:10',
            'editColor' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'editPosition' => 'integer|min:0',
        ], [], [
            'editLabel' => 'ชื่อหมวดหมู่',
            'editShort' => 'รหัสย่อ',
            'editColor' => 'สี',
            'editPosition' => 'ลำดับที่',
        ]);

        Category::where('slug', $this->editingId)->update([
            'label' => trim($this->editLabel),
            'short' => trim($this->editShort),
            'color' => $this->editColor,
            'position' => $this->editPosition,
        ]);

        $this->editingId = null;
        $this->dispatch('toast', message: 'บันทึกสำเร็จ');
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->resetValidation(['editLabel', 'editShort', 'editColor', 'editPosition']);
    }

    public function addCategory(): void
    {
        abort_unless(auth()->user()->hasPermission('categories', 'add'), 403);
        $this->validate([
            'newLabel' => 'required|string|max:100',
            'newShort' => 'required|string|max:10',
            'newColor' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'newPosition' => 'integer|min:0',
        ], [], [
            'newLabel' => 'ชื่อหมวดหมู่',
            'newShort' => 'รหัสย่อ',
            'newColor' => 'สี',
            'newPosition' => 'ลำดับที่',
        ]);

        // Generate slug from label
        $slug = str(trim($this->newLabel))->slug();

        Category::create([
            'slug' => $slug,
            'label' => trim($this->newLabel),
            'short' => trim($this->newShort),
            'color' => $this->newColor,
            'position' => $this->newPosition > 0 ? $this->newPosition : (Category::max('position') ?? 0) + 1,
        ]);

        $this->newLabel = '';
        $this->newShort = '';
        $this->newColor = '#000000';
        $this->newPosition = 0;
        $this->dispatch('toast', message: 'เพิ่มหมวดหมู่สำเร็จ');
    }

    public function deleteCategory(string $slug): void
    {
        abort_unless(auth()->user()->hasPermission('categories', 'delete'), 403);
        Category::where('slug', $slug)->delete();
        $this->dispatch('toast', message: 'ลบสำเร็จ');
    }

    public function render()
    {
        $user = auth()->user();
        return view('livewire.category-list-page', [
            'categories' => Category::orderBy('position')->get(),
            'canAdd'     => $user->hasPermission('categories', 'add'),
            'canEdit'    => $user->hasPermission('categories', 'edit'),
            'canDelete'  => $user->hasPermission('categories', 'delete'),
        ]);
    }
}
