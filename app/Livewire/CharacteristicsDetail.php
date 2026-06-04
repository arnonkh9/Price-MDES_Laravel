<?php

namespace App\Livewire;

use App\Models\CharacteristicsTemplate;
use App\Support\CompareCart;
use App\Support\Specs;
use Livewire\Attributes\On;
use Livewire\Component;

class CharacteristicsDetail extends Component
{
    public ?string $characteristicsId = null;
    public bool $show = false;
    public string $tab = 'characteristics';

    #[On('open-characteristics-detail')]
    public function open(string $id)
    {
        $this->characteristicsId = $id;
        $this->tab = 'characteristics';
        $this->show = true;
    }

    public function close()
    {
        $this->show = false;
        $this->characteristicsId = null;
    }

    public function editCharacteristics()
    {
        $id = $this->characteristicsId;
        $this->close();
        $this->dispatch('open-characteristics-form', id: $id);
    }

    public function useCompare()
    {
        abort_unless(auth()->user()->hasPermission('compare', 'view'), 403);
        CompareCart::setBaseSpec($this->characteristicsId);
        $this->redirect(route('compare'), navigate: true);
    }

    public function render()
    {
        $spec = $this->characteristicsId ? CharacteristicsTemplate::with('histories')->find($this->characteristicsId) : null;

        return view('livewire.characteristics-detail', [
            'spec' => $spec,
            'groups' => Specs::groups(),
            'color' => $spec ? Specs::color($spec->category) : '#64748B',
            'catLabel' => $spec ? Specs::label($spec->category) : '',
            'canCompare' => auth()->user()->hasPermission('compare', 'view'),
        ]);
    }
}
