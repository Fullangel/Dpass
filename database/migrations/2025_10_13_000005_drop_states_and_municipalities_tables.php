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
        // Primero eliminar las claves foráneas de headquarters
        if (Schema::hasTable('headquarters')) {
            Schema::table('headquarters', function (Blueprint $table) {
                // Eliminar clave foránea si existe
                $table->dropForeign(['municipality_id']);
                $table->dropColumn('municipality_id');
            });
        }

        // Eliminar tabla municipalities
        Schema::dropIfExists('municipalities');
        
        // Eliminar tabla states
        Schema::dropIfExists('states');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Recrear tabla states
        if (!Schema::hasTable('states')) {
            Schema::create('states', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->timestamps();
                $table->index('name', 'idx_states_name');
            });
        }

        // Recrear tabla municipalities
        if (!Schema::hasTable('municipalities')) {
            Schema::create('municipalities', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->unsignedBigInteger('state_id');
                $table->timestamps();
                $table->index('state_id', 'idx_municipalities_state_id');
                $table->foreign('state_id')->references('id')->on('states')->onDelete('cascade');
            });
        }

        // Restaurar columna municipality_id en headquarters
        if (Schema::hasTable('headquarters')) {
            Schema::table('headquarters', function (Blueprint $table) {
                $table->unsignedBigInteger('municipality_id')->after('name');
                $table->foreign('municipality_id')->references('id')->on('municipalities')->onDelete('cascade');
            });
        }
    }
};