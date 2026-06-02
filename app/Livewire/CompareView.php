<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\CharacteristicsTemplate;
use App\Support\CompareCart;
use App\Support\Specs;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('เปรียบเทียบสินค้า | ระบบราคากลาง')]
class CompareView extends Component
{
    public function setBaseSpec(string $id)
    {
        CompareCart::setBaseSpec($id);
        $this->dispatch('cart-updated');
    }

    public function clearBaseSpec()
    {
        CompareCart::setBaseSpec(null);
        $this->dispatch('cart-updated');
    }

    public function remove(string $id)
    {
        CompareCart::remove($id);
        $this->dispatch('cart-updated');
    }

    public function clear()
    {
        CompareCart::clear();
        $this->dispatch('cart-updated');
    }

    public function render()
    {
        $ids = CompareCart::ids();
        $items = collect($ids)->map(fn ($id) => Product::find($id))->filter()->values();

        $baseSpec = CompareCart::baseSpecId() ? CharacteristicsTemplate::find(CompareCart::baseSpecId()) : null;

        // Build rows: only fields with data in baseSpec or any product, grouped.
        $rows = [];
        foreach (Specs::groups() as $group) {
            $fields = collect($group['fields'])->filter(function ($f) use ($baseSpec, $items) {
                if ($baseSpec && ! empty($baseSpec->specs[$f] ?? null)) {
                    return true;
                }
                return $items->contains(fn ($p) => ! empty($p->specs[$f] ?? null));
            });
            if ($fields->isNotEmpty()) {
                $rows[] = ['type' => 'group', 'label' => $group['label']];
                foreach ($fields as $f) {
                    $rows[] = ['type' => 'field', 'field' => $f];
                }
            }
        }

        return view('livewire.compare-view', [
            'items' => $items,
            'baseSpec' => $baseSpec,
            'rows' => $rows,
            'colors' => Specs::colorMap(),
            'specs' => CharacteristicsTemplate::all(),
        ]);
    }
}
