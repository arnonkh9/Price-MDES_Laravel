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
        Schema::create('product_attachments', function (Blueprint $table) {
            $table->id();
            $table->string('product_id');
            $table->string('file_path');
            $table->string('original_name');
            $table->integer('file_size');
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamps();

            // Foreign key
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_attachments');
    }
};
