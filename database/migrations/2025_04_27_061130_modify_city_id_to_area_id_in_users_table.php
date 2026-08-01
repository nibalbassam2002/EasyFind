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
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign('users_city_id_foreign');
            });
        } catch (\Exception $e) {
            logger('Could not drop foreign key city_id for users: ' . $e->getMessage());
        }

        Schema::table('users', function (Blueprint $table) {
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
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign('users_area_id_foreign');
            });
        } catch (\Exception $e) {
            logger('Could not drop foreign key area_id for users during rollback: ' . $e->getMessage());
        }

        Schema::table('users', function (Blueprint $table) {
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
