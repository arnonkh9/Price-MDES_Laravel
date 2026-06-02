<?php

namespace Tests\Feature;

use App\Models\CharacteristicsTemplate;
use App\Models\Comparison;
use App\Models\ComparisonVendor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PdfExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\CategorySeeder']);
        // Static menu-permission cache survives between tests in the same process.
        User::clearMenuCache();
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function spec(array $overrides = []): CharacteristicsTemplate
    {
        return CharacteristicsTemplate::factory()->create($overrides);
    }

    private function comparison(array $overrides = []): Comparison
    {
        $cmp = Comparison::factory()->create($overrides);
        for ($i = 1; $i <= 3; $i++) {
            ComparisonVendor::factory()->create([
                'comparison_id' => $cmp->id,
                'position' => $i,
                'name' => "Vendor {$i}",
                'price' => 25000 + ($i * 1000),
                'specs' => ['Processor' => "Intel Core i7-{$i}000"],
            ]);
        }

        return $cmp;
    }

    // ---- Access control ----

    public function test_guest_cannot_export_spec_pdf()
    {
        $spec = $this->spec();

        $response = $this->get(route('specs.export.pdf', $spec));

        $this->assertContains($response->status(), [302, 401]);
    }

    public function test_guest_cannot_export_comparison_pdf()
    {
        $cmp = $this->comparison();

        $response = $this->get(route('comparisons.export.pdf', $cmp));

        $this->assertContains($response->status(), [302, 401]);
    }

    public function test_user_without_export_permission_is_forbidden()
    {
        // role 'user' has no Role row → no menu permissions → no export rights
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('specs.export.pdf', $this->spec()))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('comparisons.export.pdf', $this->comparison()))
            ->assertForbidden();
    }

    // ---- Happy path ----

    public function test_admin_can_download_spec_pdf()
    {
        $response = $this->actingAs($this->admin())
            ->get(route('specs.export.pdf', $this->spec()));

        $response->assertOk();
        $this->assertStringContainsString(
            '.pdf',
            (string) $response->headers->get('content-disposition')
        );
    }

    public function test_admin_can_download_comparison_pdf()
    {
        $response = $this->actingAs($this->admin())
            ->get(route('comparisons.export.pdf', $this->comparison()));

        $response->assertOk();
        $this->assertStringContainsString(
            '.pdf',
            (string) $response->headers->get('content-disposition')
        );
    }

    // ---- Regression: array-typed spec values must not crash the renderer ----
    // Before the Specs::display() guard these threw
    // "htmlspecialchars(): Argument #1 must be of type string, array given".

    public function test_spec_pdf_renders_when_a_spec_value_is_an_array()
    {
        $spec = $this->spec([
            'specs' => ['Processor' => ['Intel', 'AMD'], 'RAM' => '16GB'],
        ]);

        $this->actingAs($this->admin())
            ->get(route('specs.export.pdf', $spec))
            ->assertOk();
    }

    public function test_comparison_pdf_renders_when_a_vendor_spec_value_is_an_array()
    {
        $cmp = Comparison::factory()->create();
        ComparisonVendor::factory()->create([
            'comparison_id' => $cmp->id,
            'position' => 1,
            'name' => 'Vendor 1',
            'price' => 25000,
            'specs' => ['Processor' => ['Intel', 'AMD']],
        ]);

        $this->actingAs($this->admin())
            ->get(route('comparisons.export.pdf', $cmp))
            ->assertOk();
    }
}
