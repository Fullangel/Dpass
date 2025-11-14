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
        Schema::table('headquarters', function (Blueprint $table) {
            // Agregar columna dependency_id después de region_id
            if (!Schema::hasColumn('headquarters', 'dependency_id')) {
                $table->unsignedBigInteger('dependency_id')->nullable()->after('region_id');
                
                // Agregar clave foránea a dependencies
                $table->foreign('dependency_id')
                      ->references('id')
                      ->on('dependencies')
                      ->onDelete('set null');
                
                // Índice para búsquedas por dependencia
                $table->index('dependency_id');
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
        Schema::table('headquarters', function (Blueprint $table) {
            if (Schema::hasColumn('headquarters', 'dependency_id')) {
                $table->dropForeign(['dependency_id']);
                $table->dropColumn('dependency_id');
            }
        });
    }
};
