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
        if (Schema::hasColumn('properties', 'city_id')) {
            try {
                Schema::table('properties', function (Blueprint $table) {
                    $table->dropForeign('properties_city_id_foreign');
                });
            } catch (\Exception $e) {
                logger('Could not drop foreign key city_id: ' . $e->getMessage());
            }

            Schema::table('properties', function (Blueprint $table) {
                $table->dropColumn('city_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            if (!Schema::hasColumn('properties', 'city_id')) {
                $table->unsignedBigInteger('city_id')->nullable();
            }
        });
    }
};
