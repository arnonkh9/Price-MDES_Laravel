<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->string('slug')->primary();          // e.g. 'Notebook'
            $table->string('label');
            $table->string('short');
            $table->string('color')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->string('id')->primary();             // e.g. 'nb-001'
            $table->string('category');                  // category slug
            $table->string('brand');
            $table->string('model');
            $table->decimal('price', 14, 2)->default(0);
            $table->string('price_unit')->nullable();
            $table->string('price_date')->nullable();    // Buddhist-era string e.g. '2569-05-21'
            $table->string('price_ref')->nullable();
            $table->jsonb('specs')->nullable();          // field => value
            $table->timestamps();
            $table->index('category');
        });

        Schema::create('product_edit_histories', function (Blueprint $table) {
            $table->id();
            $table->string('product_id');
            $table->string('date')->nullable();
            $table->string('user')->nullable();
            $table->string('action')->nullable();
            $table->text('detail')->nullable();
            $table->string('source')->nullable();
            $table->text('url')->nullable();
            $table->timestamps();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });

        Schema::create('spec_templates', function (Blueprint $table) {
            $table->string('id')->primary();             // e.g. 'sp-001'
            $table->string('name');
            $table->string('category');
            $table->text('purpose')->nullable();
            $table->decimal('budget', 14, 2)->default(0);
            $table->string('year')->nullable();          // '2569'
            $table->string('month')->nullable();         // '05'
            $table->string('created_date')->nullable();
            $table->string('created_by')->nullable();
            $table->jsonb('specs')->nullable();
            $table->timestamps();
        });

        Schema::create('spec_template_histories', function (Blueprint $table) {
            $table->id();
            $table->string('spec_template_id');
            $table->string('date')->nullable();
            $table->string('user')->nullable();
            $table->string('action')->nullable();
            $table->text('detail')->nullable();
            $table->timestamps();
            $table->foreign('spec_template_id')->references('id')->on('spec_templates')->cascadeOnDelete();
        });

        Schema::create('comparisons', function (Blueprint $table) {
            $table->string('id')->primary();             // e.g. 'cmp-001'
            $table->string('name');
            $table->string('category');
            $table->string('year')->nullable();
            $table->string('month')->nullable();
            $table->string('spec_template_id')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('draft');  // draft | final
            $table->string('created_date')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();
            $table->foreign('spec_template_id')->references('id')->on('spec_templates')->nullOnDelete();
        });

        Schema::create('comparison_vendors', function (Blueprint $table) {
            $table->id();
            $table->string('comparison_id');
            $table->unsignedTinyInteger('position');     // 1..3
            $table->string('name')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->decimal('price', 14, 2)->default(0);
            $table->jsonb('specs')->nullable();
            $table->timestamps();
            $table->foreign('comparison_id')->references('id')->on('comparisons')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comparison_vendors');
        Schema::dropIfExists('comparisons');
        Schema::dropIfExists('spec_template_histories');
        Schema::dropIfExists('spec_templates');
        Schema::dropIfExists('product_edit_histories');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
