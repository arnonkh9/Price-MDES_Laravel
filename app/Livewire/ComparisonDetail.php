<?php

namespace App\Livewire;

use App\Models\Comparison;
use App\Models\CharacteristicsTemplate;
use App\Support\Specs;
use Livewire\Attributes\On;
use Livewire\Component;

class ComparisonDetail extends Component
{
    public ?string $cmpId = null;
    public bool $show = false;

    #[On('open-comparison-detail')]
    public function open(string $id)
    {
        $this->cmpId = $id;
        $this->show = true;
    }

    public function close()
    {
        $this->show = false;
        $this->cmpId = null;
    }

    public function editComparison()
    {
        $id = $this->cmpId;
        $this->close();
        $this->dispatch('open-comparison-form', id: $id);
    }

    public function render()
    {
        $cmp = $this->cmpId ? Comparison::with('vendors')->find($this->cmpId) : null;
        $spec = $cmp && $cmp->characteristics_template_id ? CharacteristicsTemplate::find($cmp->characteristics_template_id) : null;

        $rows = [];
        if ($cmp) {
            // แถวสเปค = union ของ key จากสเปคอ้างอิง + vendors (label = ชื่อ key)
            $fields = collect(Specs::comparisonFieldKeys(
                $spec?->specs,
                $cmp->vendors->pluck('specs')
            ))->filter(function ($f) use ($spec, $cmp) {
                if ($spec && ! empty($spec->specs[$f] ?? null)) {
                    return true;
                }
                return $cmp->vendors->contains(fn ($v) => ! empty($v->specs[$f] ?? null));
            });

            if ($fields->isNotEmpty()) {
                $rows[] = ['type' => 'group', 'label' => 'ข้อมูลจำเพาะ'];
                foreach ($fields as $f) {
                    $rows[] = ['type' => 'field', 'field' => $f];
                }
            }
        }

        return view('livewire.comparison-detail', [
            'cmp' => $cmp,
            'spec' => $spec,
            'rows' => $rows,
            'color' => $cmp ? Specs::color($cmp->category) : '#64748B',
            'catLabel' => $cmp ? Specs::label($cmp->category) : '',
            'minPrice' => $cmp ? $cmp->vendors->map(fn ($v) => (float) $v->price)->filter(fn ($p) => $p > 0)->min() : null,
        ]);
    }
}
