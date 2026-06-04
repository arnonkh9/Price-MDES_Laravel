<?php

namespace Tests\Feature;

use App\Livewire\CompareView;
use App\Livewire\ProductList;
use App\Models\MenuPermission;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ComparePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolePermissionSeeder']);
        User::clearMenuCache();
    }

    public function test_viewer_can_open_compare_by_default()
    {
        $viewer = User::factory()->create(['role' => 'viewer']);

        Livewire::actingAs($viewer)
            ->test(CompareView::class)
            ->assertOk();
    }

    public function test_role_without_compare_permission_is_forbidden_on_compare_page()
    {
        $this->denyCompareFor('viewer');
        $viewer = User::factory()->create(['role' => 'viewer']);

        Livewire::actingAs($viewer)
            ->test(CompareView::class)
            ->assertForbidden();
    }

    public function test_role_without_compare_permission_cannot_toggle_compare()
    {
        $this->denyCompareFor('viewer');
        $viewer = User::factory()->create(['role' => 'viewer']);
        $product = Product::factory()->create();

        Livewire::actingAs($viewer)
            ->test(ProductList::class)
            ->call('toggleCompare', $product->id)
            ->assertForbidden();
    }

    public function test_admin_always_allowed_even_if_row_disabled()
    {
        $this->denyCompareFor('admin'); // admin bypasses via isAdmin()
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(CompareView::class)
            ->assertOk();
    }

    /** Disable the compare menu permission for a role and reset the static cache. */
    private function denyCompareFor(string $slug): void
    {
        $role = Role::where('slug', $slug)->first();
        MenuPermission::where('role_id', $role->id)
            ->where('menu_key', 'compare')
            ->update(['can_see' => false]);
        User::clearMenuCache();
    }
}
