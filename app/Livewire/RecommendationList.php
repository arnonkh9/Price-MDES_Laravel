<?php

namespace App\Livewire;

use App\Models\RecommendationItem;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('ข้อแนะนำประกอบการพิจารณา | ระบบราคากลาง')]
class RecommendationList extends Component
{
    public ?int $editingId = null;
    public string $editContent = '';
    public string $newContent = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->canSeeMenu('recommendations'), 403);
    }

    public function startEdit(int $id): void
    {
        abort_unless(auth()->user()->hasPermission('recommendations', 'edit'), 403);
        $item = RecommendationItem::find($id);
        if (! $item) {
            return;
        }
        $this->editingId = $id;
        $this->editContent = $item->content;
        $this->resetValidation(['editContent']);
    }

    public function saveEdit(): void
    {
        abort_unless(auth()->user()->hasPermission('recommendations', 'edit'), 403);
        $this->validate(['editContent' => 'required|string'], [], ['editContent' => 'เนื้อหา']);
        RecommendationItem::where('id', $this->editingId)->update(['content' => trim($this->editContent)]);
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
        abort_unless(auth()->user()->hasPermission('recommendations', 'add'), 403);
        $this->validate(['newContent' => 'required|string'], [], ['newContent' => 'เนื้อหา']);
        RecommendationItem::create([
            'content' => trim($this->newContent),
            'position' => (RecommendationItem::max('position') ?? 0) + 1,
        ]);
        $this->newContent = '';
        $this->dispatch('toast', message: 'เพิ่มข้อใหม่สำเร็จ');
    }

    public function deleteItem(int $id): void
    {
        abort_unless(auth()->user()->hasPermission('recommendations', 'delete'), 403);
        RecommendationItem::destroy($id);
        $this->dispatch('toast', message: 'ลบสำเร็จ');
    }

    public function render()
    {
        $user = auth()->user();
        return view('livewire.recommendation-list', [
            'items'     => RecommendationItem::orderBy('position')->get(),
            'canAdd'    => $user->hasPermission('recommendations', 'add'),
            'canEdit'   => $user->hasPermission('recommendations', 'edit'),
            'canDelete' => $user->hasPermission('recommendations', 'delete'),
        ]);
    }
}
