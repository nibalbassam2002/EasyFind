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
            try {
              
                $table->dropForeign(['city_id']);
            } catch (\Exception $e) {
                logger('Could not drop foreign key city_id for properties: ' . $e->getMessage());
            }
           $table->renameColumn('city_id', 'area_id');

        
            $table->unsignedBigInteger('area_id')->nullable()->change(); 
           $table->foreign('area_id')
                 ->references('id')
                 ->on('areas')
                 ->constrained() 
                 ->nullOnDelete(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            try {
                $table->dropForeign(['area_id']);
            } catch (\Exception $e) {
                logger('Could not drop foreign key area_id for properties during rollback: ' . $e->getMessage());
            }
            $table->renameColumn('area_id', 'city_id');

       
             try {
                $table->unsignedBigInteger('city_id')->nullable()->change();
                $table->foreign('city_id')
                     ->references('id')
                     ->on('cities') 
                     ->nullOnDelete();
            } catch (\Exception $e) {
                logger('Could not re-add foreign key city_id for properties during rollback: ' . $e->getMessage());
            }
        });
    }
};
