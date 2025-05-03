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
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم المنطقة (قد يتكرر بين المحافظات)
            $table->foreignId('governorate_id') // مفتاح أجنبي للمحافظة
                  ->constrained('governorates') // يربط بجدول المحافظات
                  ->onDelete('cascade'); // احذف المناطق إذا حذفت المحافظة
            $table->timestamps();
            $table->unique(['name', 'governorate_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('areas');
    }
};
