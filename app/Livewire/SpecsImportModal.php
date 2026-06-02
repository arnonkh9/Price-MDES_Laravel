<?php

namespace App\Livewire;

use App\Imports\CharacteristicsImport;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class SpecsImportModal extends Component
{
    use WithFileUploads;

    public bool $show = false;
    public $file;
    public ?string $result = null;
    public bool $processing = false;

    // Preview step state
    public string $step = 'upload';       // 'upload' | 'preview'
    public array  $previewRows   = [];
    public int    $previewValid  = 0;
    public int    $previewSkipped = 0;

    #[On('open-specs-import')]
    public function open()
    {
        abort_unless(auth()->user()->hasPermission('specs', 'import'), 403);
        $this->reset(['file', 'result', 'processing', 'previewRows', 'previewValid', 'previewSkipped']);
        $this->step = 'upload';
        $this->show = true;
    }

    public function close()
    {
        $this->show = false;
        $this->step = 'upload';
        $this->previewRows = [];
        $this->previewValid = $this->previewSkipped = 0;
    }

    /**
     * Step 1: parse & validate the file without saving (dry-run).
     * Transitions to 'preview' step.
     */
    public function preview()
    {
        abort_unless(auth()->user()->hasPermission('specs', 'import'), 403);

        // Avoid re-validating the Livewire temp file (causes Flysystem UnableToRetrieveMetadata).
        // The file was already validated on upload; the @disabled button guards against null.
        if (!$this->file) {
            $this->addError('file', 'กรุณาเลือกไฟล์');
            return;
        }

        $import = new CharacteristicsImport;
        $import->dryRun = true;
        Excel::import($import, $this->file->getRealPath());

        $this->previewRows    = $import->previewRows;
        $this->previewValid   = collect($this->previewRows)->where('status', 'valid')->count();
        $this->previewSkipped = collect($this->previewRows)->where('status', 'invalid')->count();
        $this->step = 'preview';
    }

    /**
     * Go back to upload step and reset preview state.
     */
    public function backToUpload()
    {
        $this->step = 'upload';
        $this->previewRows   = [];
        $this->previewValid  = $this->previewSkipped = 0;
        $this->reset('file');
    }

    /**
     * Step 2: perform actual import (re-reads the same temp file).
     */
    public function import()
    {
        abort_unless(auth()->user()->hasPermission('specs', 'import'), 403);

        if (!$this->file) {
            $this->addError('file', 'กรุณาเลือกไฟล์');
            return;
        }

        $this->processing = true;
        $import = new CharacteristicsImport;
        Excel::import($import, $this->file->getRealPath());
        $this->processing = false;

        $this->result = "นำเข้าสำเร็จ {$import->imported} รายการ" . ($import->skipped ? " · ข้าม {$import->skipped} แถว (ข้อมูลไม่ครบ)" : '');
        $this->step = 'upload';
        $this->previewRows = [];
        $this->previewValid = $this->previewSkipped = 0;
        $this->reset('file');
        $this->dispatch('specs-imported');
        $this->dispatch('toast', message: $this->result);
    }

    public function downloadSample()
    {
        return redirect()->route('specs.sample');
    }

    public function render()
    {
        return view('livewire.specs-import-modal');
    }
}
