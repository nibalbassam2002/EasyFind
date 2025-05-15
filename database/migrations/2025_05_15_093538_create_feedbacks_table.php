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
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
            $table->string('subject')->nullable(); 
            $table->enum('type', ['complaint', 'suggestion', 'improvement', 'other'])->default('other'); // نوع الملاحظة
            $table->text('message'); // نص الملاحظة
            $table->enum('status', ['new', 'seen', 'replied', 'resolved'])->default('new'); // حالة الملاحظة
            $table->text('admin_reply')->nullable(); // رد المشرف
            $table->foreignId('replied_by')->nullable()->constrained('users')->onDelete('set null'); // المشرف الذي رد
            $table->timestamp('replied_at')->nullable(); // تاريخ الرد
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};
