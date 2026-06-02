<?php

namespace App\Livewire;

use App\Imports\ProductsImport;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class ImportModal extends Component
{
    use WithFileUploads;

    public bool $show = false;
    public $file;
    public ?string $result = null;
    public bool $processing = false;

    // Step state machine: 'upload' | 'preview'
    public string $step = 'upload';
    public array  $previewRows   = [];
    public int    $previewValid  = 0;
    public int    $previewSkipped = 0;

    #[On('open-import')]
    public function open()
    {
        abort_unless(auth()->user()->hasPermission('products', 'import'), 403);
        $this->step = 'upload';
        $this->previewRows = [];
        $this->previewValid = $this->previewSkipped = 0;
        $this->reset(['file', 'result', 'processing']);
        $this->show = true;
    }

    public function close()
    {
        $this->show = false;
        $this->step = 'upload';
        $this->previewRows = [];
        $this->previewValid = $this->previewSkipped = 0;
        $this->reset('file');
    }

    public function preview()
    {
        abort_unless(auth()->user()->hasPermission('products', 'import'), 403);
        $this->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ], [], ['file' => 'ไฟล์']);

        $this->processing = true;
        $import = new ProductsImport();
        $import->dryRun = true;
        Excel::import($import, $this->file->getRealPath());
        $this->processing = false;

        $this->previewRows    = $import->previewRows;
        $this->previewValid   = count(array_filter($this->previewRows, fn($r) => $r['status'] === 'valid'));
        $this->previewSkipped = count(array_filter($this->previewRows, fn($r) => $r['status'] !== 'valid'));

        $this->step = 'preview';
    }

    public function backToUpload()
    {
        $this->step = 'upload';
        $this->previewRows = [];
        $this->previewValid = $this->previewSkipped = 0;
        $this->reset('file');
    }

    public function import()
    {
        abort_unless(auth()->user()->hasPermission('products', 'import'), 403);
        $this->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ], [], ['file' => 'ไฟล์']);

        $this->processing = true;
        $import = new ProductsImport;
        Excel::import($import, $this->file->getRealPath());
        $this->processing = false;

        $this->result = "นำเข้าสำเร็จ {$import->imported} รายการ".($import->skipped ? " · ข้าม {$import->skipped} แถว (ข้อมูลไม่ครบ)" : '');
        $this->step = 'upload';
        $this->previewRows = [];
        $this->previewValid = $this->previewSkipped = 0;
        $this->reset('file');
        $this->dispatch('product-saved');
        $this->dispatch('toast', message: $this->result);
    }

    public function downloadSample()
    {
        return redirect()->route('products.sample');
    }

    public function render()
    {
        return view('livewire.import-modal');
    }
}
