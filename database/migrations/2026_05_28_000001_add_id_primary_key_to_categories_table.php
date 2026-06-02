<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PostgreSQL (production): in-place DDL — keeps existing rows untouched.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE categories ADD COLUMN id BIGSERIAL');         // 1. auto-increment id
            DB::statement('ALTER TABLE categories DROP CONSTRAINT categories_pkey'); // 2. drop slug PK
            DB::statement('ALTER TABLE categories ADD PRIMARY KEY (id)');            // 3. id becomes PK
            DB::statement('ALTER TABLE categories ADD CONSTRAINT categories_slug_unique UNIQUE (slug)'); // 4. slug stays unique

            return;
        }

        // SQLite / other drivers (test suite): ALTER TABLE can't change the primary
        // key, so rebuild the table. Data-preserving even if rows already exist.
        $this->rebuild(function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('label');
            $table->string('short');
            $table->string('color')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        }, dropId: false);
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE categories DROP CONSTRAINT IF EXISTS categories_slug_unique');
            DB::statement('ALTER TABLE categories DROP CONSTRAINT categories_pkey');
            DB::statement('ALTER TABLE categories DROP COLUMN id');
            DB::statement('ALTER TABLE categories ADD PRIMARY KEY (slug)');

            return;
        }

        $this->rebuild(function (Blueprint $table) {
            $table->string('slug')->primary();
            $table->string('label');
            $table->string('short');
            $table->string('color')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        }, dropId: true);
    }

    /**
     * Rebuild the categories table via rename → create → copy → drop.
     * Used for drivers (SQLite) that can't alter the primary key in place.
     */
    private function rebuild(callable $schema, bool $dropId): void
    {
        $rows = DB::table('categories')->get();

        Schema::rename('categories', 'categories_legacy_tmp');
        Schema::create('categories', $schema);

        foreach ($rows as $row) {
            $data = (array) $row;
            if ($dropId) {
                unset($data['id']); // reverting to slug PK
            }
            DB::table('categories')->insert($data);
        }

        Schema::dropIfExists('categories_legacy_tmp');
    }
};
