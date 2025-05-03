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
        Schema::table('properties', function (Blueprint $table) {
            //
            $statuses = ['pending', 'approved', 'rejected', 'rented', 'sold', 'unavailable']; // يمكنك تعديل هذه القائمة حسب الحاجة

            // إضافة عمود الحالة الجديد
            $table->enum('status', $statuses)
                  ->default('pending')       // القيمة الافتراضية عند إضافة عقار جديد
                  ->after('views_count')     // (اختياري) تحديد مكان العمود في الجدول
                  ->comment('حالة العقار (مراجعة، موافق عليه، مرفوض، مؤجر، مباع، غير متاح)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            //
            $table->dropColumn('status');
        });
    }
};
