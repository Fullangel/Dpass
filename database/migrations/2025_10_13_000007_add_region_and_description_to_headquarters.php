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
        if (Schema::hasTable('headquarters')) {
            Schema::table('headquarters', function (Blueprint $table) {
                // Agregar campo description
                if (!Schema::hasColumn('headquarters', 'description')) {
                    $table->text('description')->nullable()->after('name');
                }

                // Agregar campo region_id después de eliminar municipality_id
                if (!Schema::hasColumn('headquarters', 'region_id')) {
                    $table->unsignedBigInteger('region_id')->nullable()->after('description');
                    
                    // Crear clave foránea
                    $table->foreign('region_id')
                        ->references('id')->on('regions')
                        ->onDelete('set null');
                        
                    $table->index('region_id', 'idx_headquarters_region_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('headquarters')) {
            Schema::table('headquarters', function (Blueprint $table) {
                // Eliminar clave foránea e índice
                $table->dropForeign(['region_id']);
                $table->dropIndex(['idx_headquarters_region_id']);
                
                // Eliminar columnas
                $table->dropColumn(['region_id', 'description']);
            });
        }
    }
};