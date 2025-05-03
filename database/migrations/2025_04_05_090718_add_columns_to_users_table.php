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
        Schema::table('users', function (Blueprint $table) {
            //
            $table->string('phone')->nullable()->after('password');
            $table->foreignId('city_id')->nullable()->constrained();
            $table->string('address')->nullable();
            $table->enum('role', ['admin',
                'content_moderator', 
                'property_lister',
                'customer'])->default('customer');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('profile_image')->nullable();
            $table->text('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->dropColumn([
                'phone',
                'city_id',
                'address',
                'role',
                'status',
                'profile_image',
                'description'
            ]);

            $table->dropForeign(['city_id']);
        });
    }
};
