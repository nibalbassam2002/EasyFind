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
        Schema::table('messages', function (Blueprint $table) {
            // نستخدم change() لتعديل العمود
            // نجعله string، ونعطيه طولاً مناسباً، ونجعله nullable ونضيف index لتحسين البحث
            $table->string('type', 50)->nullable()->index()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // هذا الكود سيعيد العمود إلى حالته السابقة إذا احتجنا للتراجع
            $table->string('type')->nullable()->change();
        });
    }
};