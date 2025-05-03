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
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->comment('المستخدم صاحب الطلب');
            $table->foreignId('property_id')->nullable()->constrained()->comment('العقار المعني');
            // 'inquiry': يعني أن الطلب هو استفسار عن العقار.'viewing': يعني أن الطلب هو طلب معاينة للعقار.'contract': يعني أن الطلب هو طلب عقد إيجار أو بيع للعقار.
            $table->enum('type', ['inquiry', 'viewing', 'contract'])->default('inquiry'); 
            $table->text('message');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
