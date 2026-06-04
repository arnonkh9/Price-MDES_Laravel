<?php

namespace App\Livewire;

use App\Models\Comparison;
use App\Models\Product;
use App\Models\CharacteristicsTemplate;
use App\Support\GeneratesUUID;
use App\Support\Specs;
use Livewire\Attributes\On;
use Livewire\Component;

class ComparisonForm extends Component
{
    use GeneratesUUID;
    public bool $show = false;
    public ?string $editingId = null;
    public string $tab = 'info';

    public string $name = '';
    public string $category = 'Notebook';
    public string $year = '';
    public string $month = '05';
    public ?string $specTemplateId = '';
    public string $notes = '';
    public string $status = 'draft';
    public string $created_date = '2569-05-21';

    // Filtered characteristics based on selected category
    public array $filteredCharacteristics = [];

    // 3 vendors: each ['name','brand','model','price','specs'=>[]]
    public array $vendors = [];

    // ค่า input "เพิ่มรายการสเปค" แยกตาม index ของ vendor (0..2)
    public array $newSpecField = ['', '', ''];

    protected function rules(): array
    {
        return ['name' => 'required|string'];
    }

    protected $messages = ['name.required' => 'กรุณากรอกชื่อ'];

    #[On('open-comparison-form')]
    public function open(?string $id = null)
    {
        $user = auth()->user();
        $canAdd  = $user->hasPermission('comparisons', 'add');
        $canEdit = $user->hasPermission('comparisons', 'edit');
        abort_unless($canAdd || $canEdit, 403);
        $this->resetValidation();
        $this->tab = 'info';

        if ($id && ($c = Comparison::with('vendors')->find($id))) {
            $this->editingId = $c->id;
            $this->name = $c->name;
            $this->category = $c->category;
            $this->year = $c->year ?: '2569';
            $this->month = $c->month ?? '';
            $this->specTemplateId = $c->characteristics_template_id ?? '';
            $this->notes = $c->notes ?? '';
            $this->status = $c->status ?? 'draft';
            $this->created_date = $c->created_date ?? '2569-05-21';
            $this->vendors = [];
            for ($i = 0; $i < 3; $i++) {
                $v = $c->vendors->firstWhere('position', $i + 1);
                $this->vendors[$i] = [
                    // best-effort: ผูกกลับไปยังสินค้าต้นทาง (match brand+model+price)
                    'product_id' => $v ? ($this->matchProductId($v->brand, $v->model, $v->price) ?? '') : '',
                    'name' => $v->name ?? '',
                    'brand' => $v->brand ?? '',
                    'model' => $v->model ?? '',
                    'price' => $v ? $v->price : '',
                    'specs' => $v->specs ?? [],
                ];
            }
        } else {
            $this->editingId = null;
            $this->name = '';
            $this->category = 'Notebook';
            $this->year = '';
            $this->month = '';
            $this->specTemplateId = '';
            $this->notes = '';
            $this->status = 'draft';
            $this->created_date = '2569-05-21';
            $this->vendors = array_fill(0, 3, ['product_id' => '', 'name' => '', 'brand' => '', 'model' => '', 'price' => '', 'specs' => []]);
        }
        // Populate the characteristics dropdown for the loaded category so that
        // editing an existing comparison shows (and lets the user change) the
        // reference spec template instead of a disabled/empty dropdown.
        $this->loadFilteredCharacteristics();
        $this->show = true;
    }

    /** Load characteristics templates for the current category (used by open() + updatedCategory()). */
    private function loadFilteredCharacteristics(): void
    {
        $this->filteredCharacteristics = $this->category
            ? CharacteristicsTemplate::where('category', $this->category)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->toArray()
            : [];
    }

    public function close()
    {
        $this->show = false;
        $this->editingId = null;
    }

    /**
     * When a vendor's product_id select changes:
     *  - มีค่า → กันเลือกซ้ำกับเจ้าอื่น แล้ว auto-fill brand/model/price/specs
     *  - ค่าว่าง (ล้าง) → reset ฟิลด์ของ vendor นั้นให้กรอกเองได้
     */
    public function updatedVendors($value, $key)
    {
        if (! str_ends_with($key, '.product_id')) {
            return;
        }
        $i = (int) explode('.', $key)[0];

        if ($value) {
            // กันเลือกซ้ำ: ถ้าสินค้านี้ถูกเจ้าอื่นเลือกไว้ → ปฏิเสธ + เตือน
            foreach ($this->vendors as $j => $v) {
                if ($j !== $i && ($v['product_id'] ?? '') === $value) {
                    $this->vendors[$i]['product_id'] = '';
                    $this->dispatch('toast', message: 'สินค้านี้ถูกเลือกในอีกเจ้าแล้ว', type: 'warn');

                    return;
                }
            }
            if ($p = Product::find($value)) {
                $this->vendors[$i]['name'] = $p->brand;   // company name = product brand (locked)
                $this->vendors[$i]['brand'] = $p->brand;
                $this->vendors[$i]['model'] = $p->model;
                $this->vendors[$i]['price'] = $p->price;   // offered price = central price (locked)
                $this->vendors[$i]['specs'] = $p->specs ?? [];
            }
        } else {
            // ล้าง → reset ให้กรอกเองได้
            $this->vendors[$i] = ['product_id' => '', 'name' => '', 'brand' => '', 'model' => '', 'price' => '', 'specs' => []];
        }
    }

    /** ปุ่มล้าง/เปลี่ยนสินค้า — programmatic set ไม่ trigger updated hook จึง reset เองทั้งหมด */
    public function clearVendorProduct(int $i): void
    {
        $this->vendors[$i] = ['product_id' => '', 'name' => '', 'brand' => '', 'model' => '', 'price' => '', 'specs' => []];
    }

    /** ดึงข้อมูลสินค้าล่าสุด (ราคา + สเปค) จาก products table มาอัปเดต vendor โดยไม่เปลี่ยนสินค้าที่เลือก */
    public function refreshVendorProduct(int $i): void
    {
        $pid = $this->vendors[$i]['product_id'] ?? '';
        if (! $pid) {
            return;
        }
        if ($p = Product::find($pid)) {
            $this->vendors[$i]['name']  = $p->brand;
            $this->vendors[$i]['brand'] = $p->brand;
            $this->vendors[$i]['model'] = $p->model;
            $this->vendors[$i]['price'] = $p->price;
            $this->vendors[$i]['specs'] = $p->specs ?? [];
            $this->dispatch('toast', message: 'ดึงข้อมูลสินค้าล่าสุดแล้ว');
        }
    }

    /** หา product id ที่ตรงกับ brand+model (+price) เพื่อ restore dropdown ตอนแก้ไข */
    private function matchProductId(?string $brand, ?string $model, $price): ?string
    {
        if (! $brand && ! $model) {
            return null;
        }

        return Product::query()
            ->where('brand', $brand)
            ->where('model', $model)
            ->where('price', (float) $price)
            ->value('id')
            ?? Product::query()
                ->where('brand', $brand)
                ->where('model', $model)
                ->value('id');
    }

    /** เพิ่มแถวสเปคใหม่ให้ vendor ตาม index (กรณีกรอกเอง / ไม่มี template) */
    public function addSpecField(int $i): void
    {
        $key = trim($this->newSpecField[$i] ?? '');
        if ($key === '' || isset($this->vendors[$i]['specs'][$key])) {
            $this->newSpecField[$i] = '';
            return;
        }
        $this->vendors[$i]['specs'][$key] = '';
        $this->newSpecField[$i] = '';
    }

    /** Reset spec template เมื่อเปลี่ยนปี — สเปคใหม่อาจไม่มีในปีเดิม */
    public function updatedYear(): void
    {
        $this->specTemplateId = '';
    }

    /** When category changes, filter characteristics by category */
    public function updatedCategory(): void
    {
        $this->loadFilteredCharacteristics();
        // Reset selected characteristic when category changes
        $this->specTemplateId = null;
    }

    /** Auto-fill ชื่อการเปรียบเทียบจากชื่อ spec template ที่เลือก */
    public function updatedSpecTemplateId(): void
    {
        if ($this->specTemplateId) {
            $spec = CharacteristicsTemplate::find($this->specTemplateId);
            if ($spec) {
                $this->name = $spec->name;
            }
        }
    }

    /** ลบแถวสเปคออกจาก vendor ตาม index */
    public function removeSpecField(int $i, string $key): void
    {
        unset($this->vendors[$i]['specs'][$key]);
    }

    public function save()
    {
        $user = auth()->user();
        $isNew = ! $this->editingId;
        abort_unless($isNew ? $user->hasPermission('comparisons', 'add') : $user->hasPermission('comparisons', 'edit'), 403);
        if (trim($this->name) === '') {
            $this->tab = 'info';
            $this->validate();
        }

        // กันบันทึกข้อมูลซ้ำ: product_id ซ้ำ หรือ brand+model ซ้ำกันระหว่างเจ้า
        $ids = collect($this->vendors)->pluck('product_id')->filter();
        $pairs = collect($this->vendors)
            ->filter(fn ($v) => trim(($v['brand'] ?? '').($v['model'] ?? '')) !== '')
            ->map(fn ($v) => mb_strtolower(trim(($v['brand'] ?? '').'|'.($v['model'] ?? ''))));
        if ($ids->count() !== $ids->unique()->count() || $pairs->count() !== $pairs->unique()->count()) {
            $this->dispatch('toast', message: 'มีสินค้า/ผู้ขายซ้ำกัน กรุณาเลือกให้แตกต่างกัน', type: 'warn');

            return;
        }

        $isEdit = (bool) $this->editingId;
        $existing = $this->editingId ? Comparison::find($this->editingId) : null;
        $createdBy = $existing?->created_by ?? auth()->user()->name;

        $cmp = Comparison::updateOrCreate(
            ['id' => $this->editingId ?: self::generateID('cmp')],
            [
                'name' => $this->name,
                'category' => $this->category,
                'year' => $this->year,
                'month' => $this->month,
                'characteristics_template_id' => $this->specTemplateId ?: null,
                'notes' => $this->notes,
                'status' => $this->status,
                'created_date' => $this->created_date,
                'created_by' => $createdBy,
            ]
        );

        $cmp->vendors()->delete();
        foreach ($this->vendors as $i => $v) {
            $cmp->vendors()->create([
                'position' => $i + 1,
                'name' => $v['name'] ?? '',
                'brand' => $v['brand'] ?? '',
                'model' => $v['model'] ?? '',
                'price' => (float) ($v['price'] ?: 0),
                'specs' => array_filter($v['specs'] ?? [], fn ($x) => $x !== null && $x !== ''),
            ]);
        }

        $this->close();
        $this->dispatch('comparison-saved');
        $this->dispatch('toast', message: 'บันทึกการเปรียบเทียบสำเร็จ');
    }

    public function render()
    {
        // สเปคอ้างอิงจาก template ที่เลือก (ใช้กำหนดแถวพื้นฐาน)
        $refSpecs = $this->specTemplateId
            ? (CharacteristicsTemplate::find($this->specTemplateId)?->specs ?? [])
            : [];

        // แถวสเปค = union ของ key จากสเปคอ้างอิง + 3 vendors (label = ชื่อ key)
        $vendorSpecs = collect($this->vendors)->map(fn ($v) => $v['specs'] ?? []);
        $fieldKeys = Specs::comparisonFieldKeys($refSpecs, $vendorSpecs);

        return view('livewire.comparison-form', [
            'categories' => Specs::categories(),
            'months' => Specs::months(),
            'years' => Specs::years(),
            'specTemplates' => CharacteristicsTemplate::where('year', $this->year)
                ->orderBy('name')
                ->get(),
            'fieldKeys' => $fieldKeys,
            'filteredCharacteristics' => $this->filteredCharacteristics,
        ]);
    }

    #[\Livewire\Attributes\Computed]
    public function filteredProducts()
    {
        return Product::where('category', $this->category)
            ->orderBy('brand')
            ->orderBy('model')
            ->get();
    }
}
