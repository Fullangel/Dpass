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
        Schema::create('dependencies', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre de la dependencia
            $table->unsignedBigInteger('region_id'); // ID de la región relacionada
            $table->timestamps();
            
            // Índice para búsquedas por región
            $table->index('region_id');
            
            // Clave foránea a la tabla regions
            $table->foreign('region_id')
                  ->references('id')
                  ->on('regions')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dependencies');
    }
};
