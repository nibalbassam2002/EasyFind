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
        if (Schema::hasTable('properties')) {
            Schema::table('properties', function (Blueprint $table) {

                $table->unsignedInteger('land_area')->nullable()->after('area')->comment('Land area in sqm (for houses, villas, land)');
                $table->enum('property_condition', ['new', 'used', 'needs_renovation'])->nullable()->after('location')->comment('Condition (new, used, needs renovation)');
                $table->enum('finishing_type', ['full', 'semi', 'none'])->nullable()->after('property_condition')->comment('Finishing level (full, semi, none)');
                $table->json('amenities')->nullable()->after('finishing_type')->comment('Available amenities (JSON array)');
                $table->string('view_type')->nullable()->after('amenities')->comment('View (sea, garden, street, etc.)');
                $table->enum('commercial_type', ['office', 'shop', 'warehouse'])->nullable()->after('caravan_type')->comment('Type if commercial property'); // تم التأكد من مكان الإضافة
                $table->string('commercial_purpose')->nullable()->after('commercial_type')->comment('Suitable purpose for commercial property');
                $table->text('additional_details')->nullable()->after('commercial_purpose')->comment('Additional details (e.g., land status)');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       
        if (Schema::hasTable('properties')) {
            Schema::table('properties', function (Blueprint $table) {
                $columnsToDrop = [
                    'additional_details', 'commercial_purpose', 'commercial_type',
                    'view_type', 'amenities', 'finishing_type',
                    'property_condition', 'land_area'
                ];
                 foreach ($columnsToDrop as $column) {
                    if (Schema::hasColumn('properties', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
         }
    }
};
