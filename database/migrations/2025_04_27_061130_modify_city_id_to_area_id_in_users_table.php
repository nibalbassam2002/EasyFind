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
            try {
                $table->dropForeign(['city_id']);
            } catch (\Exception $e) {
                logger('Could not drop foreign key city_id for users: ' . $e->getMessage());
            }


           $table->renameColumn('city_id', 'area_id');


            $table->foreign('area_id')
                 ->references('id')
                 ->on('areas')
                 ->nullOnDelete(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            try {
                $table->dropForeign(['area_id']);
           } catch (\Exception $e) {
                logger('Could not drop foreign key area_id for users during rollback: ' . $e->getMessage());
           }

           $table->renameColumn('area_id', 'city_id');


            try {
                $table->foreign('city_id')
                     ->references('id')
                     ->on('cities') 
                     ->nullOnDelete();
           } catch (\Exception $e) {
               logger('Could not re-add foreign key city_id for users during rollback (maybe cities table doesnt exist?): ' . $e->getMessage());
           }
        });
    }
};
