<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use App\Support\Specs;
use Livewire\Attributes\On;
use Livewire\Component;

class CategoryManager extends Component
{
    public bool $show = false;

    // new category form
    public string $newLabel = '';
    public string $newShort = '';
    public string $newColor = '#2563EB';

    // edit mode
    public ?string $editingSlug = null;
    public string $editLabel = '';
    public string $editShort = '';
    public string $editColor = '';

    #[On('open-categories')]
    public function open()
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $this->reset(['newLabel', 'newShort']);
        $this->newColor = Specs::palette()[0];
        $this->show = true;
    }

    public function close()
    {
        $this->show = false;
    }

    public function startEdit(string $slug): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $cat = Category::where('slug', $slug)->first();
        if (! $cat) {
            return;
        }
        $this->editingSlug = $slug;
        $this->editLabel = $cat->label;
        $this->editShort = $cat->short;
        $this->editColor = $cat->color ?? '#2563EB';
    }

    public function saveEdit(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $this->validate([
            'editLabel' => 'required|string',
            'editShort' => 'required|string|max:6',
        ], [], [
            'editLabel' => 'ชื่อหมวดหมู่',
            'editShort' => 'ชื่อย่อ',
        ]);

        Category::where('slug', $this->editingSlug)->update([
            'label' => $this->editLabel,
            'short' => strtoupper($this->editShort),
            'color' => $this->editColor,
        ]);

        $this->editingSlug = null;
        $this->dispatch('toast', message: 'อัปเดตหมวดหมู่สำเร็จ');
    }

    public function cancelEdit(): void
    {
        $this->editingSlug = null;
        $this->resetValidation(['editLabel', 'editShort']);
    }

    public function setColor(string $slug, string $color)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        // If editing this category, update preview color instead of DB directly
        if ($this->editingSlug === $slug) {
            $this->editColor = $color;

            return;
        }

        Category::where('slug', $slug)->update(['color' => $color]);
        $this->dispatch('toast', message: 'อัปเดตสีสำเร็จ');
    }

    public function addCategory()
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $this->validate([
            'newLabel' => 'required|string',
            'newShort' => 'required|string|max:6',
            'newColor' => 'required|string',
        ], [], [
            'newLabel' => 'ชื่อหมวดหมู่',
            'newShort' => 'ชื่อย่อ',
        ]);

        $slug = \Illuminate\Support\Str::slug($this->newLabel) ?: 'cat-'.now()->valueOf();
        Category::updateOrCreate(['slug' => $slug], [
            'label' => $this->newLabel,
            'short' => strtoupper($this->newShort),
            'color' => $this->newColor,
            'position' => (Category::max('position') ?? 0) + 1,
        ]);

        $this->reset(['newLabel', 'newShort']);
        $this->dispatch('toast', message: 'เพิ่มหมวดหมู่สำเร็จ');
    }

    public function deleteCategory(string $slug)
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $count = Product::where('category', $slug)->count();
        if ($count > 0) {
            $this->dispatch('toast', message: "ไม่สามารถลบได้ มีสินค้า {$count} รายการในหมวดนี้", type: 'warn');

            return;
        }
        Category::where('slug', $slug)->delete();
        $this->dispatch('toast', message: 'ลบหมวดหมู่สำเร็จ');
    }

    public function render()
    {
        $cats = Specs::categories();
        $counts = Product::selectRaw('category, count(*) as c')->groupBy('category')->pluck('c', 'category')->toArray();

        return view('livewire.category-manager', [
            'categories' => $cats,
            'counts' => $counts,
            'palette' => Specs::palette(),
        ]);
    }
}
