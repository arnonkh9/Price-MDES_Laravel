<?php

namespace App\Livewire;

use App\Models\GuidelineItem;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('แนวทางการพิจารณาเบื้องต้น | ระบบราคากลาง')]
class GuidelineList extends Component
{
    public ?int $editingId = null;
    public string $editContent = '';
    public string $newContent = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->canSeeMenu('guidelines'), 403);
    }

    public function startEdit(int $id): void
    {
        abort_unless(auth()->user()->hasPermission('guidelines', 'edit'), 403);
        $item = GuidelineItem::find($id);
        if (! $item) {
            return;
        }
        $this->editingId = $id;
        $this->editContent = $item->content;
        $this->resetValidation(['editContent']);
    }

    public function saveEdit(): void
    {
        abort_unless(auth()->user()->hasPermission('guidelines', 'edit'), 403);
        $this->validate(['editContent' => 'required|string'], [], ['editContent' => 'เนื้อหา']);
        GuidelineItem::where('id', $this->editingId)->update(['content' => trim($this->editContent)]);
        $this->editingId = null;
        $this->dispatch('toast', message: 'บันทึกสำเร็จ');
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->resetValidation(['editContent']);
    }

    public function addItem(): void
    {
        abort_unless(auth()->user()->hasPermission('guidelines', 'add'), 403);
        $this->validate(['newContent' => 'required|string'], [], ['newContent' => 'เนื้อหา']);
        GuidelineItem::create([
            'content' => trim($this->newContent),
            'position' => (GuidelineItem::max('position') ?? 0) + 1,
        ]);
        $this->newContent = '';
        $this->dispatch('toast', message: 'เพิ่มข้อใหม่สำเร็จ');
    }

    public function deleteItem(int $id): void
    {
        abort_unless(auth()->user()->hasPermission('guidelines', 'delete'), 403);
        GuidelineItem::destroy($id);
        $this->dispatch('toast', message: 'ลบสำเร็จ');
    }

    public function render()
    {
        $user = auth()->user();
        return view('livewire.guideline-list', [
            'items'     => GuidelineItem::orderBy('position')->get(),
            'canAdd'    => $user->hasPermission('guidelines', 'add'),
            'canEdit'   => $user->hasPermission('guidelines', 'edit'),
            'canDelete' => $user->hasPermission('guidelines', 'delete'),
        ]);
    }
}
