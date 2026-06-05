<?php

namespace Tests\Feature;

use App\Livewire\ComparisonForm;
use App\Models\Comparison;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ComparisonVendorCountTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_comparison_starts_with_three_vendors()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(ComparisonForm::class)
            ->call('open')
            ->assertCount('vendors', 3);
    }

    public function test_add_vendor_increments_up_to_five_max()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(ComparisonForm::class)
            ->call('open')
            ->call('addVendor')->assertCount('vendors', 4)
            ->call('addVendor')->assertCount('vendors', 5)
            ->call('addVendor')->assertCount('vendors', 5); // capped at 5
    }

    public function test_remove_vendor_down_to_three_min()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(ComparisonForm::class)
            ->call('open')
            ->call('addVendor')->call('addVendor')->assertCount('vendors', 5)
            ->call('removeVendor', 4)->assertCount('vendors', 4)
            ->call('removeVendor', 3)->assertCount('vendors', 3)
            ->call('removeVendor', 0)->assertCount('vendors', 3); // cannot go below 3
    }

    public function test_save_persists_five_vendors_with_positions()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $vendors = [];
        for ($i = 0; $i < 5; $i++) {
            $vendors[] = [
                'product_id' => '',
                'name'  => 'บริษัท '.($i + 1),
                'brand' => 'Brand'.($i + 1),
                'model' => 'Model'.($i + 1),
                'price' => 10000 + $i * 1000,
                'specs' => [],
            ];
        }

        Livewire::actingAs($admin)
            ->test(ComparisonForm::class)
            ->call('open')
            ->set('name', 'เปรียบเทียบ 5 ราย')
            ->set('year', '2569')
            ->set('vendors', $vendors)
            ->call('save')
            ->assertHasNoErrors();

        $cmp = Comparison::where('name', 'เปรียบเทียบ 5 ราย')->with('vendors')->first();
        $this->assertNotNull($cmp);
        $this->assertCount(5, $cmp->vendors);
        $this->assertEqualsCanonicalizing(
            [1, 2, 3, 4, 5],
            $cmp->vendors->pluck('position')->all()
        );
    }

    public function test_editing_existing_three_vendor_comparison_loads_three()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $cmp = Comparison::factory()->create(['category' => 'Notebook']);
        for ($i = 1; $i <= 3; $i++) {
            $cmp->vendors()->create([
                'position' => $i, 'name' => "V$i", 'brand' => "B$i",
                'model' => "M$i", 'price' => 1000 * $i, 'specs' => [],
            ]);
        }

        Livewire::actingAs($admin)
            ->test(ComparisonForm::class)
            ->call('open', $cmp->id)
            ->assertCount('vendors', 3);
    }

    public function test_export_excel_and_pdf_with_five_vendors_return_ok()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $cmp = Comparison::factory()->create(['category' => 'Notebook', 'name' => 'cmp5']);
        for ($i = 1; $i <= 5; $i++) {
            $cmp->vendors()->create([
                'position' => $i, 'name' => "V$i", 'brand' => "B$i",
                'model' => "M$i", 'price' => 1000 * $i, 'specs' => ['CPU' => "spec$i"],
            ]);
        }

        $xlsx = $this->actingAs($admin)->get(route('comparisons.export', $cmp->id));
        $xlsx->assertOk();
        $this->assertStringContainsString('.xlsx', $xlsx->headers->get('content-disposition'));

        $pdf = $this->actingAs($admin)->get(route('comparisons.export.pdf', $cmp->id));
        $pdf->assertOk();
        $this->assertStringContainsString('.pdf', $pdf->headers->get('content-disposition'));
    }
}
