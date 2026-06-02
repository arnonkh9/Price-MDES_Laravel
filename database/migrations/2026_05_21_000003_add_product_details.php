<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('price_source')->nullable()->after('price_ref');  // Excel, Manual, Website, API, Other
            $table->text('price_url')->nullable()->after('price_source');     // Reference URL
            $table->text('notes')->nullable()->after('price_url');            // Additional notes
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['price_source', 'price_url', 'notes']);
        });
    }
};
