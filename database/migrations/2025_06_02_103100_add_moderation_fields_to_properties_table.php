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
            $table->foreignId('moderated_by')->nullable()->after('status')->constrained('users')->onDelete('set null');
            // onDelete('set null') يعني إذا تم حذف المشرف، سيتم تعيين moderated_by إلى NULL بدلاً من حذف العقار

            // إضافة عمود لتاريخ ووقت المراجعة
            $table->timestamp('moderated_at')->nullable()->after('moderated_by');

            // إضافة عمود لسبب الرفض (إذا تم رفض العقار)
            $table->text('rejection_reason')->nullable()->after('moderated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropForeign(['moderated_by']); // أو $table->dropForeign('properties_moderated_by_foreign');
            $table->dropColumn('moderated_by');
            $table->dropColumn('moderated_at');
            $table->dropColumn('rejection_reason');
        });
    }
};
