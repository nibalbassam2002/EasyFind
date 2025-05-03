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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('كود العقار مثل: PROP-2024-001');
            $table->foreignId('user_id')->constrained();
            $table->foreignId('category_id')->constrained()->comment('التصنيف الرئيسي');
            $table->foreignId('sub_category_id')->constrained('categories')->comment('التصنيف الفرعي');
            $table->string('title');
            $table->text('description');
            $table->enum('purpose', ['rent', 'sale', 'lease'])->default('sale');
            $table->decimal('price', 12, 2);
            $table->enum('currency', ['ILS', 'USD', 'JOD'])->default('ILS');
            $table->foreignId('city_id')->constrained();
            $table->string('address');
            $table->string('location')->nullable()->comment('الإحداثيات الجغرافية');
            $table->integer('area')->comment('المساحة بالمتر المربع');
            $table->integer('rooms')->nullable()->comment('للبيوت والشقق');
            $table->integer('bathrooms')->nullable();
            $table->integer('floors')->nullable();
            $table->string('land_type')->nullable()->comment('لبيع الأراضي');
            $table->string('tent_type')->nullable()->comment('لبيع الخيام');
            $table->string('caravan_type')->nullable()->comment('لبيع الكرفانات');
            $table->json('images')->nullable();
            $table->string('video_url')->nullable();
            $table->decimal('rating', 3, 2)->nullable()->default(0)->comment('متوسط التقييم من 5');
            $table->integer('views_count')->default(0)->comment('عدد المشاهدات');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
