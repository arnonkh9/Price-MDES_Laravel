<?php

namespace Tests\Feature;

use App\Livewire\ComparisonForm;
use App\Livewire\ComparisonList;
use App\Models\CharacteristicsTemplate;
use App\Models\Comparison;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ComparisonFormCharacteristicsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Regression: editing a comparison left $filteredCharacteristics empty,
     * so the reference-spec dropdown was disabled/empty even though templates
     * existed for that category. open() must now preload them.
     */
    public function test_editing_comparison_loads_filtered_characteristics()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $spec = CharacteristicsTemplate::factory()->create(['category' => 'Notebook']);
        $comparison = Comparison::factory()->create([
            'category' => 'Notebook',
            'characteristics_template_id' => $spec->id,
        ]);

        Livewire::actingAs($admin)
            ->test(ComparisonForm::class)
            ->call('open', $comparison->id)
            ->assertSet('specTemplateId', $spec->id)
            ->assertCount('filteredCharacteristics', 1);
    }

    /**
     * Regression: toggleSelectAll used a count-based check that disagreed with
     * the diff-based $allSelected in render(). When selectedIds held items
     * outside the current filter, "select all" wrongly cleared the selection.
     */
    public function test_toggle_select_all_selects_current_when_selection_is_stale()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $c1 = Comparison::factory()->create(['name' => 'AAA']);
        $c2 = Comparison::factory()->create(['name' => 'BBB']);

        $component = Livewire::actingAs($admin)
            ->test(ComparisonList::class)
            ->set('selectedIds', ['stale-1', 'stale-2', 'stale-3']) // 3 stale ids, none visible
            ->call('toggleSelectAll');

        $selected = $component->get('selectedIds');
        sort($selected);
        $expected = [$c1->id, $c2->id];
        sort($expected);

        // With the fix: selecting all visible items (not clearing them).
        $this->assertEquals($expected, $selected);
    }
}
