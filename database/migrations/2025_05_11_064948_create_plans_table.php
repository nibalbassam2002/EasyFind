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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('price', 8, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->unsignedInteger('duration_in_days')->default(30)->comment('0 for lifetime/free, 30 for monthly, 365 for yearly');
            $table->text('description')->nullable();
            $table->json('features')->nullable()->comment('{"max_properties": 5, "allowed_types": ["tent"], "featured_slots": 0}');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
