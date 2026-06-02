<?php

namespace App\Livewire;

use App\Models\CharacteristicsTemplate;
use App\Models\CharacteristicsTemplateHistory;
use App\Support\GeneratesUUID;
use App\Support\Specs;
use Livewire\Attributes\On;
use Livewire\Component;

class CharacteristicsForm extends Component
{
    use GeneratesUUID;
    public bool $show = false;
    public ?string $editingId = null;
    public string $section = 'basic';

    public string $name = '';
    public string $category = 'Notebook';
    public string $purpose = '';
    public $budget = '';
    public string $year = '2569';
    public string $month = '05';
    public string $created_date = '2569-05-21';
    public array $specs = [];

    protected function rules(): array
    {
        return ['name' => 'required|string'];
    }

    protected $messages = ['name.required' => 'กรุณากรอกชื่อคุณลักษณะพื้นฐาน'];

    #[On('open-characteristics-form')]
    public function open(?string $id = null)
    {
        $user = auth()->user();
        $canAdd  = $user->hasPermission('specs', 'add');
        $canEdit = $user->hasPermission('specs', 'edit');
        abort_unless($canAdd || $canEdit, 403);
        $this->resetValidation();
        $this->section = 'basic';

        if ($id && ($s = CharacteristicsTemplate::find($id))) {
            $this->editingId = $s->id;
            $this->name = $s->name;
            $this->category = $s->category;
            $this->purpose = $s->purpose ?? '';
            $this->budget = $s->budget;
            $this->year = $s->year ?: '2569';
            $this->month = $s->month ?? '';
            $this->created_date = $s->created_date ?? '2569-05-21';
            $this->specs = $s->specs ?? [];
        } else {
            $this->editingId = null;
            $this->name = '';
            $this->category = 'Notebook';
            $this->purpose = '';
            $this->budget = '';
            $this->year = '2569';
            $this->month = '05';
            $this->created_date = '2569-05-21';
            $this->specs = [];
        }
        $this->show = true;
    }

    public function close()
    {
        $this->show = false;
        $this->editingId = null;
    }

    public function save()
    {
        $user = auth()->user();
        $isNew = ! $this->editingId;
        abort_unless($isNew ? $user->hasPermission('specs', 'add') : $user->hasPermission('specs', 'edit'), 403);
        $this->validate();

        $isEdit = (bool) $this->editingId;
        $cleanSpecs = collect($this->specs)
            ->map(fn ($v) => is_array($v) ? implode(', ', $v) : (string) $v)
            ->filter(fn ($v) => $v !== '')
            ->all();
        $existing = $this->editingId ? CharacteristicsTemplate::find($this->editingId) : null;
        $createdBy = $existing?->created_by ?? auth()->user()->name;

        $spec = CharacteristicsTemplate::updateOrCreate(
            ['id' => $this->editingId ?: self::generateID('sp')],
            [
                'name' => $this->name,
                'category' => $this->category,
                'purpose' => $this->purpose,
                'budget' => (float) ($this->budget ?: 0),
                'year' => $this->year,
                'month' => $this->month,
                'created_date' => $this->created_date,
                'created_by' => $createdBy,
                'specs' => $cleanSpecs,
            ]
        );

        $spec->histories()->create([
            'date' => $this->created_date,
            'user' => auth()->user()->name,
            'action' => $isEdit ? 'แก้ไขคุณลักษณะพื้นฐาน' : 'สร้างคุณลักษณะพื้นฐานใหม่',
            'detail' => $this->name,
        ]);

        $this->close();
        $this->dispatch('characteristics-saved');
        $this->dispatch('toast', message: $isEdit ? 'บันทึกการแก้ไขคุณลักษณะพื้นฐานสำเร็จ' : 'สร้างคุณลักษณะพื้นฐานใหม่สำเร็จ');
    }

    public function render()
    {
        return view('livewire.characteristics-form', [
            'groups' => Specs::groups(),
            'categories' => Specs::categories(),
            'months' => Specs::months(),
            'years' => Specs::years(),
            'color' => Specs::color($this->category),
            'previewLabel' => Specs::label($this->category),
            'totalSpecCount' => collect($this->specs)->filter()->count(),
        ]);
    }
}
