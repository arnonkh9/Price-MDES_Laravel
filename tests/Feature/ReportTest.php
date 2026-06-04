<?php

namespace Tests\Feature;

use App\Livewire\ReportPage;
use App\Models\MenuPermission;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Support\Reports;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\CategorySeeder']);
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolePermissionSeeder']);
        User::clearMenuCache();
    }

    public function test_build_returns_expected_shape_for_all_types()
    {
        Product::factory()->count(3)->create(['category' => 'Notebook', 'price' => 25000, 'price_date' => '2569-05-01']);

        foreach (array_keys(Reports::types()) as $type) {
            $report = Reports::build($type, ['year' => 'all', 'category' => 'all']);
            $this->assertArrayHasKey('columns', $report, "type=$type");
            $this->assertArrayHasKey('rows', $report, "type=$type");
            $this->assertArrayHasKey('kpis', $report, "type=$type");
            $this->assertArrayHasKey('chart', $report, "type=$type");
            $this->assertNotEmpty($report['columns'], "type=$type columns");
        }
    }

    public function test_admin_can_view_report_page_and_switch_types()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $component = Livewire::actingAs($admin)->test(ReportPage::class)->assertOk();

        foreach (array_keys(Reports::types()) as $type) {
            $component->call('setType', $type)->assertSet('reportType', $type)->assertOk();
        }
    }

    public function test_viewer_can_view_report_page_by_default()
    {
        $viewer = User::factory()->create(['role' => 'viewer']);

        Livewire::actingAs($viewer)->test(ReportPage::class)->assertOk();
    }

    public function test_role_without_reports_permission_is_forbidden()
    {
        $role = Role::where('slug', 'viewer')->first();
        MenuPermission::where('role_id', $role->id)->where('menu_key', 'reports')->update(['can_see' => false]);
        User::clearMenuCache();

        $viewer = User::factory()->create(['role' => 'viewer']);

        Livewire::actingAs($viewer)->test(ReportPage::class)->assertForbidden();
    }

    public function test_admin_can_export_report_pdf_and_excel()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Product::factory()->count(2)->create(['category' => 'Notebook', 'price' => 30000, 'price_date' => '2569-05-01']);

        $pdf = $this->actingAs($admin)->get(route('reports.export.pdf', ['type' => 'price']));
        $pdf->assertOk();
        $this->assertStringContainsString('.pdf', $pdf->headers->get('content-disposition'));

        $xlsx = $this->actingAs($admin)->get(route('reports.export.excel', ['type' => 'spec']));
        $xlsx->assertOk();
        $this->assertStringContainsString('.xlsx', $xlsx->headers->get('content-disposition'));
    }

    public function test_role_without_reports_permission_cannot_export()
    {
        $role = Role::where('slug', 'viewer')->first();
        MenuPermission::where('role_id', $role->id)->where('menu_key', 'reports')->update(['can_see' => false]);
        User::clearMenuCache();

        $viewer = User::factory()->create(['role' => 'viewer']);

        $this->actingAs($viewer)->get(route('reports.export.pdf', ['type' => 'price']))->assertForbidden();
    }
}
