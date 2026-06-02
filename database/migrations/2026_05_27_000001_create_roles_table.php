<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 50)->unique();        // ตรงกับ users.role string
            $table->string('name', 100);                 // ชื่อแสดงผลภาษาไทย
            $table->text('description')->nullable();
            $table->string('level', 20)->default('viewer'); // admin|editor|viewer — กำหนด canEdit()
            $table->boolean('is_system')->default(false); // true = ลบไม่ได้
            $table->integer('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
