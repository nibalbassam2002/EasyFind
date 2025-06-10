<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // إضافة أعمدة لجدول العقارات
        Schema::table('properties', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->after('status');
            $table->timestamp('featured_at')->nullable()->after('is_featured');
        });

        // إضافة عمود لجدول الاشتراكات
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->unsignedInteger('featured_slots_used')->default(0)->after('properties_listed_count');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['is_featured', 'featured_at']);
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('featured_slots_used');
        });
    }
};