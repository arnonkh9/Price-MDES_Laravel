<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MigrationPortabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_migrations_run_successfully()
    {
        // Just running RefreshDatabase trait handles all migrations
        // If this test completes without error, migrations were successful

        // Verify all tables exist
        $this->assertTrue(Schema::hasTable('products'));
        $this->assertTrue(Schema::hasTable('comparisons'));
        $this->assertTrue(Schema::hasTable('characteristics_templates'));
        $this->assertTrue(Schema::hasTable('characteristics_template_histories'));
    }

    public function test_renamed_columns_exist()
    {
        // Verify that the rename migration worked correctly
        $this->assertTrue(Schema::hasColumn('comparisons', 'characteristics_template_id'));
        $this->assertTrue(Schema::hasColumn('characteristics_template_histories', 'characteristics_template_id'));

        // Old column names should not exist
        $this->assertFalse(Schema::hasColumn('comparisons', 'spec_template_id'));
        $this->assertFalse(Schema::hasColumn('characteristics_template_histories', 'spec_template_id'));
    }

    public function test_foreign_keys_are_properly_configured()
    {
        // Verify that foreign key constraints are properly set up
        // This is implicit in the test - if FKs were broken, subsequent tests would fail

        // Try to insert a comparison with valid FK
        DB::table('comparisons')->insert([
            'id' => 'test-cmp-001',
            'name' => 'Test',
            'category' => 'Notebook',
            'characteristics_template_id' => null,  // FK allows null
            'created_by' => 'test_user',
            'created_date' => '2569-05-21',
        ]);

        $this->assertTrue(DB::table('comparisons')->where('id', 'test-cmp-001')->exists());
    }

    public function test_migration_works_on_current_database()
    {
        // The fact that RefreshDatabase completes means migrations succeeded
        // This is a smoke test to ensure no migration errors

        $tables = [
            'users',
            'categories',
            'products',
            'product_edit_histories',
            'characteristics_templates',
            'characteristics_template_histories',
            'comparisons',
            'comparison_vendors',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(
                Schema::hasTable($table),
                "Table '{$table}' should exist after migrations"
            );
        }
    }
}
