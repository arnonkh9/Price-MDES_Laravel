<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // Rename tables (works on both PostgreSQL and SQLite)
        DB::statement('ALTER TABLE spec_template_histories RENAME TO characteristics_template_histories');
        DB::statement('ALTER TABLE spec_templates RENAME TO characteristics_templates');

        // Rename columns in both tables using Schema builder (database-agnostic)
        Schema::table('comparisons', function (Blueprint $table) {
            $table->renameColumn('spec_template_id', 'characteristics_template_id');
        });

        Schema::table('characteristics_template_histories', function (Blueprint $table) {
            $table->renameColumn('spec_template_id', 'characteristics_template_id');
        });

        // Recreate foreign keys - handle SQLite vs PostgreSQL differently
        if (DB::getDriverName() !== 'sqlite') {
            // PostgreSQL: drop and recreate foreign keys by name
            Schema::table('comparisons', function (Blueprint $table) {
                try {
                    $table->dropForeign('comparisons_spec_template_id_foreign');
                } catch (\Exception $e) {
                    \Log::warning('Migration: Could not drop FK comparisons_spec_template_id_foreign: ' . $e->getMessage());
                }
                $table->foreign('characteristics_template_id')
                    ->references('id')
                    ->on('characteristics_templates')
                    ->nullOnDelete();
            });

            Schema::table('characteristics_template_histories', function (Blueprint $table) {
                try {
                    $table->dropForeign('spec_template_histories_spec_template_id_foreign');
                } catch (\Exception $e) {
                    \Log::warning('Migration: Could not drop FK spec_template_histories_spec_template_id_foreign: ' . $e->getMessage());
                }
                $table->foreign('characteristics_template_id')
                    ->references('id')
                    ->on('characteristics_templates')
                    ->cascadeOnDelete();
            });
        }
        // For SQLite: foreign keys are already updated via table rename, no need to drop/recreate

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        // Rename columns back first
        Schema::table('characteristics_template_histories', function (Blueprint $table) {
            $table->renameColumn('characteristics_template_id', 'spec_template_id');
        });

        Schema::table('comparisons', function (Blueprint $table) {
            $table->renameColumn('characteristics_template_id', 'spec_template_id');
        });

        // Drop and recreate foreign keys for rollback (PostgreSQL only)
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('characteristics_template_histories', function (Blueprint $table) {
                try {
                    $table->dropForeign('characteristics_template_histories_characteristics_template_id_foreign');
                } catch (\Exception $e) {
                    \Log::warning('Migration down: Could not drop FK characteristics_template_histories_characteristics_template_id_foreign: ' . $e->getMessage());
                }
                $table->foreign('spec_template_id')
                    ->references('id')
                    ->on('spec_templates')
                    ->cascadeOnDelete();
            });

            Schema::table('comparisons', function (Blueprint $table) {
                try {
                    $table->dropForeign('comparisons_characteristics_template_id_foreign');
                } catch (\Exception $e) {
                    \Log::warning('Migration down: Could not drop FK comparisons_characteristics_template_id_foreign: ' . $e->getMessage());
                }
                $table->foreign('spec_template_id')
                    ->references('id')
                    ->on('spec_templates')
                    ->nullOnDelete();
            });
        }

        // Rename tables back (works on both PostgreSQL and SQLite)
        DB::statement('ALTER TABLE characteristics_template_histories RENAME TO spec_template_histories');
        DB::statement('ALTER TABLE characteristics_templates RENAME TO spec_templates');

        Schema::enableForeignKeyConstraints();
    }
};
