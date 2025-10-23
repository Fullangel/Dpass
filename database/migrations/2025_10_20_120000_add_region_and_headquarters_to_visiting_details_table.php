<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('visiting_details', function (Blueprint $table) {
            // Solo agregar region_id si no existe
            if (!Schema::hasColumn('visiting_details', 'region_id')) {
                $table->unsignedBigInteger('region_id')->nullable()->after('employee_id');
                
                // Agregar llave foránea
                $table->foreign('region_id')->references('id')->on('regions')->onDelete('set null');
                
                // Agregar índice para mejorar el rendimiento
                $table->index('region_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('visiting_details', function (Blueprint $table) {
            if (Schema::hasColumn('visiting_details', 'region_id')) {
                $table->dropForeign(['region_id']);
                $table->dropIndex(['region_id']);
                $table->dropColumn('region_id');
            }
        });
    }
};