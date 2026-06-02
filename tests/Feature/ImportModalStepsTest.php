<?php

namespace Tests\Feature;

use App\Livewire\ImportModal;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Verifies the 2-step product import flow:
 *   Step 1 (Upload): file input + "ตรวจสอบข้อมูล" button
 *   Step 2 (Preview): preview table + "ยืนยันนำเข้า" button
 *
 * NOTE: These tests never call import() — no data is written to the database.
 */
class ImportModalStepsTest extends TestCase
{
    public function test_upload_step_shows_check_button(): void
    {
        Livewire::test(ImportModal::class)
            ->set('show', true)
            ->set('step', 'upload')
            ->assertSee('นำเข้าข้อมูลจาก Excel')
            ->assertSee('ตรวจสอบข้อมูล')
            ->assertSee('ดาวน์โหลดตัวอย่าง');
    }

    public function test_preview_step_shows_import_button(): void
    {
        Livewire::test(ImportModal::class)
            ->set('show', true)
            ->set('step', 'preview')
            ->set('previewValid', 3)
            ->set('previewSkipped', 0)
            ->assertSee('ตรวจสอบข้อมูล')
            ->assertSee('จะนำเข้า 3 รายการ')
            ->assertSeeHtml('wire:click="import"')
            ->assertSee('ยืนยันนำเข้า')
            ->assertSee('ย้อนกลับ');
    }

    public function test_back_to_upload_resets_state(): void
    {
        Livewire::test(ImportModal::class)
            ->set('show', true)
            ->set('step', 'preview')
            ->set('previewValid', 3)
            ->call('backToUpload')
            ->assertSet('step', 'upload')
            ->assertSet('previewValid', 0)
            ->assertSee('นำเข้าข้อมูลจาก Excel');
    }
}
