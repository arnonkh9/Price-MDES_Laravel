<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('menu_permissions', function (Blueprint $table) {
            $table->boolean('can_add')->default(false)->after('can_see');
            $table->boolean('can_edit')->default(false)->after('can_add');
            $table->boolean('can_delete')->default(false)->after('can_edit');
            $table->boolean('can_import')->default(false)->after('can_delete');
            $table->boolean('can_export')->default(false)->after('can_import');
        });
    }

    public function down(): void
    {
        Schema::table('menu_permissions', function (Blueprint $table) {
            $table->dropColumn(['can_add', 'can_edit', 'can_delete', 'can_import', 'can_export']);
        });
    }
};
