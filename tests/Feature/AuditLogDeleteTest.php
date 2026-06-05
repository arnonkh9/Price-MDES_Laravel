<?php

namespace Tests\Feature;

use App\Livewire\AuditLogPage;
use App\Models\CharacteristicsTemplate;
use App\Models\CharacteristicsTemplateHistory;
use App\Models\Product;
use App\Models\ProductEditHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuditLogDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_a_product_history_record()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create();
        $log = ProductEditHistory::create([
            'product_id' => $product->id, 'date' => '2569-05-01', 'user' => 'admin',
            'action' => 'edit', 'detail' => 'test', 'source' => 'Excel',
        ]);

        Livewire::actingAs($admin)
            ->test(AuditLogPage::class)
            ->call('deleteLog', 'product', $log->id);

        $this->assertDatabaseMissing('product_edit_histories', ['id' => $log->id]);
    }

    public function test_admin_can_delete_a_spec_history_record()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $spec = CharacteristicsTemplate::factory()->create();
        $log = CharacteristicsTemplateHistory::create([
            'characteristics_template_id' => $spec->id, 'date' => '2569-05-01',
            'user' => 'admin', 'action' => 'edit', 'detail' => 'test',
        ]);

        Livewire::actingAs($admin)
            ->test(AuditLogPage::class)
            ->call('deleteLog', 'spec', $log->id);

        $this->assertDatabaseMissing('characteristics_template_histories', ['id' => $log->id]);
    }

    public function test_toggle_select_item_and_select_all()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create();
        $spec = CharacteristicsTemplate::factory()->create();
        $p1 = ProductEditHistory::create(['product_id' => $product->id, 'date' => '2569-05-01', 'user' => 'a', 'action' => 'edit', 'detail' => 'p1', 'source' => 'Excel']);
        $s1 = CharacteristicsTemplateHistory::create(['characteristics_template_id' => $spec->id, 'date' => '2569-05-01', 'user' => 'a', 'action' => 'edit', 'detail' => 's1']);

        $c = Livewire::actingAs($admin)->test(AuditLogPage::class);

        // toggle a single item on/off
        $c->call('toggleSelectItem', "product:{$p1->id}")
          ->assertSet('selectedKeys', ["product:{$p1->id}"])
          ->call('toggleSelectItem', "product:{$p1->id}")
          ->assertSet('selectedKeys', []);

        // select all rows on the current page
        $c->call('toggleSelectAll');
        $selected = $c->get('selectedKeys');
        $this->assertContains("product:{$p1->id}", $selected);
        $this->assertContains("spec:{$s1->id}", $selected);

        // toggle all off
        $c->call('toggleSelectAll')->assertSet('selectedKeys', []);
    }

    public function test_admin_can_bulk_delete_mixed_history()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create();
        $spec = CharacteristicsTemplate::factory()->create();

        $p1 = ProductEditHistory::create(['product_id' => $product->id, 'date' => '2569-05-01', 'user' => 'a', 'action' => 'edit', 'detail' => 'p1', 'source' => 'Excel']);
        $p2 = ProductEditHistory::create(['product_id' => $product->id, 'date' => '2569-05-02', 'user' => 'a', 'action' => 'edit', 'detail' => 'p2', 'source' => 'Excel']);
        $s1 = CharacteristicsTemplateHistory::create(['characteristics_template_id' => $spec->id, 'date' => '2569-05-01', 'user' => 'a', 'action' => 'edit', 'detail' => 's1']);

        Livewire::actingAs($admin)
            ->test(AuditLogPage::class)
            ->set('selectedKeys', ["product:{$p1->id}", "product:{$p2->id}", "spec:{$s1->id}"])
            ->call('bulkDelete')
            ->assertSet('selectedKeys', []);

        $this->assertDatabaseMissing('product_edit_histories', ['id' => $p1->id]);
        $this->assertDatabaseMissing('product_edit_histories', ['id' => $p2->id]);
        $this->assertDatabaseMissing('characteristics_template_histories', ['id' => $s1->id]);
    }

    public function test_non_admin_cannot_bulk_delete_history()
    {
        $viewer = User::factory()->create(['role' => 'viewer']);
        $product = Product::factory()->create();
        $log = ProductEditHistory::create(['product_id' => $product->id, 'date' => '2569-05-01', 'user' => 'a', 'action' => 'edit', 'detail' => 'x', 'source' => 'Excel']);

        Livewire::actingAs($viewer)
            ->test(AuditLogPage::class)
            ->set('selectedKeys', ["product:{$log->id}"])
            ->call('bulkDelete')
            ->assertForbidden();

        $this->assertDatabaseHas('product_edit_histories', ['id' => $log->id]);
    }

    public function test_non_admin_cannot_delete_history()
    {
        $viewer = User::factory()->create(['role' => 'viewer']);
        $product = Product::factory()->create();
        $log = ProductEditHistory::create([
            'product_id' => $product->id, 'date' => '2569-05-01', 'user' => 'someone',
            'action' => 'edit', 'detail' => 'test', 'source' => 'Excel',
        ]);

        Livewire::actingAs($viewer)
            ->test(AuditLogPage::class)
            ->call('deleteLog', 'product', $log->id)
            ->assertForbidden();

        $this->assertDatabaseHas('product_edit_histories', ['id' => $log->id]);
    }
}
