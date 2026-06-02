<?php

namespace App\Livewire;

use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductAttachment;
use App\Support\GeneratesUUID;
use App\Support\Specs;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProductForm extends Component
{
    use GeneratesUUID, WithFileUploads;
    public bool $show = false;
    public ?string $editingId = null;
    public string $section = 'basic';

    public string $category = 'Notebook';
    public string $brand = '';
    public string $model = '';
    public $price = '';
    public string $price_unit = 'บาท/เครื่อง';
    public string $price_date = '2569-05-21';
    public string $price_source = '';
    public string $price_url = '';
    public array $specs = [];

    // File uploads
    public $uploadedFiles = [];
    public array $existingAttachments = [];

    // Validation rules for file uploads
    protected $rules = [
        'uploadedFiles.*' => 'file|mimes:pdf,jpg,jpeg,png,gif,webp|max:10240', // 10MB
    ];

    protected function rules(): array
    {
        return [
            'brand' => 'required|string',
            'model' => 'required|string',
        ];
    }

    protected $messages = [
        'brand.required' => 'กรุณากรอกแบรนด์',
        'model.required' => 'กรุณากรอกรุ่น/โมเดล',
    ];

    #[On('open-product-form')]
    public function open(?string $id = null)
    {
        $user = auth()->user();
        $canAdd  = $user->hasPermission('products', 'add');
        $canEdit = $user->hasPermission('products', 'edit');
        abort_unless($canAdd || $canEdit, 403);
        $this->resetValidation();
        $this->section = 'basic';
        $this->uploadedFiles = [];
        $this->existingAttachments = [];

        if ($id && ($p = Product::find($id))) {
            $this->editingId = $p->id;
            $this->category = $p->category;
            $this->brand = $p->brand;
            $this->model = $p->model;
            $this->price = $p->price;
            $this->price_unit = $p->price_unit ?? 'บาท/เครื่อง';
            $this->price_date = $p->price_date ?? '2569-05-21';
            $this->price_source = $p->price_source ?? '';
            $this->price_url = $p->price_url ?? '';
            $this->specs = $p->specs ?? [];
            $this->existingAttachments = $p->attachments()->get()->toArray();
        } else {
            $this->editingId = null;
            $this->category = 'Notebook';
            $this->brand = '';
            $this->model = '';
            $this->price = '';
            $this->price_unit = 'บาท/เครื่อง';
            $this->price_date = '2569-05-21';
            $this->price_source = '';
            $this->price_url = '';
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
        abort_unless($isNew ? $user->hasPermission('products', 'add') : $user->hasPermission('products', 'edit'), 403);
        $this->validate();

        // Validate uploaded files
        if (!empty($this->uploadedFiles)) {
            $this->validate([
                'uploadedFiles.*' => 'required|file|mimes:pdf,jpg,jpeg,png,gif,webp|max:10240',
            ], [
                'uploadedFiles.*.mimes' => 'ไฟล์ต้องเป็น PDF หรือรูปภาพ (jpg, png, webp) เท่านั้น',
                'uploadedFiles.*.max' => 'ขนาดไฟล์ต้องไม่เกิน 10 MB',
            ]);
        }

        $isEdit = (bool) $this->editingId;
        $cleanSpecs = array_filter($this->specs, fn ($v) => $v !== null && $v !== '');

        $product = Product::updateOrCreate(
            ['id' => $this->editingId ?: self::generateID('p')],
            [
                'category' => $this->category,
                'brand' => $this->brand,
                'model' => $this->model,
                'price' => (float) ($this->price ?: 0),
                'price_unit' => $this->price_unit,
                'price_date' => $this->price_date,
                'price_source' => $this->price_source,
                'price_url' => $this->price_url,
                'specs' => $cleanSpecs,
            ]
        );

        // Handle file uploads
        foreach ($this->uploadedFiles as $file) {
            $path = $file->store('products/'.$product->id, 'public');
            ProductAttachment::create([
                'product_id' => $product->id,
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
            ]);
        }

        $product->histories()->create([
            'date' => $this->price_date ?: '2569-05-21',
            'user' => auth()->user()->name,
            'action' => $isEdit ? 'แก้ไขข้อมูล' : 'เพิ่มข้อมูลใหม่',
            'detail' => ($isEdit ? 'แก้ไข ' : 'เพิ่ม ').$this->model,
        ]);

        $this->close();
        $this->dispatch('product-saved');
        $this->dispatch('toast', message: $isEdit ? 'บันทึกการแก้ไขสำเร็จ' : 'เพิ่มสินค้าใหม่สำเร็จ');
    }

    public function removeAttachment(int $attachmentId)
    {
        abort_unless(auth()->user()->hasPermission('products', 'edit'), 403);
        $attachment = ProductAttachment::find($attachmentId);
        if ($attachment && $attachment->product_id === $this->editingId) {
            \Storage::disk('public')->delete($attachment->file_path);
            $attachment->delete();
            $this->existingAttachments = array_filter(
                $this->existingAttachments,
                fn ($a) => $a['id'] !== $attachmentId
            );
            $this->dispatch('toast', message: 'ลบไฟล์สำเร็จ');
        }
    }

    public function render()
    {
        return view('livewire.product-form', [
            'groups' => Specs::groups(),
            'categories' => Specs::categories(),
            'brandOptions' => Brand::orderBy('name')->pluck('name'),
            'color' => Specs::color($this->category),
            'previewLabel' => Specs::label($this->category),
        ]);
    }
}
