<?php

namespace App\Livewire;

use App\Models\Product;
use App\Support\CompareCart;
use App\Support\Specs;
use Livewire\Attributes\On;
use Livewire\Component;

class ProductDetail extends Component
{
    public ?string $productId = null;
    public bool $show = false;
    public string $tab = 'characteristics';

    // history form
    public string $hSource = 'Excel';
    public string $hUrl = '';
    public string $hNote = '';

    #[On('open-product-detail')]
    public function open(string $id)
    {
        $this->productId = $id;
        $this->tab = 'characteristics';
        $this->hSource = 'Excel';
        $this->hUrl = '';
        $this->hNote = '';
        $this->show = true;
    }

    public function close()
    {
        $this->show = false;
        $this->productId = null;
    }

    public function toggleCompare()
    {
        abort_unless(auth()->user()->hasPermission('compare', 'view'), 403);
        if (! $this->productId) {
            return;
        }
        $result = CompareCart::toggle($this->productId);
        if ($result === 'full') {
            $this->dispatch('toast', message: 'เปรียบเทียบได้สูงสุด 3 รายการ', type: 'warn');
        }
        $this->dispatch('cart-updated');
    }

    public function editProduct()
    {
        $id = $this->productId;
        $this->close();
        $this->dispatch('open-product-form', id: $id);
    }

    public function addHistory()
    {
        abort_unless(auth()->user()->hasPermission('products', 'edit'), 403);
        if (trim($this->hNote) === '' && trim($this->hUrl) === '') {
            return;
        }
        $product = Product::find($this->productId);
        if (! $product) {
            return;
        }
        $product->histories()->create([
            'date' => $this->buddhistToday(),
            'user' => auth()->user()->name,
            'action' => 'บันทึกข้อมูลเข้าระบบ',
            'detail' => trim($this->hNote) ?: ('นำเข้าจาก '.$this->hSource),
            'source' => $this->hSource,
            'url' => trim($this->hUrl),
        ]);
        $this->hNote = '';
        $this->hUrl = '';
        $this->hSource = 'Excel';
        $this->dispatch('toast', message: 'บันทึกประวัติสำเร็จ');
    }

    private function buddhistToday(): string
    {
        $now = now();
        return ($now->year + 543).'-'.$now->format('m-d');
    }

    public function render()
    {
        $product = $this->productId ? Product::with('histories')->find($this->productId) : null;

        return view('livewire.product-detail', [
            'product' => $product,
            'groups' => Specs::groups(),
            'color' => $product ? Specs::color($product->category) : '#64748B',
            'catLabel' => $product ? Specs::label($product->category) : '',
            'inCompare' => $product ? CompareCart::has($product->id) : false,
            'compareCount' => CompareCart::count(),
            'canCompare' => auth()->user()->hasPermission('compare', 'view'),
            'sources' => Specs::historySources(),
        ]);
    }
}
