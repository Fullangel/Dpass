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
        if (!Schema::hasTable('headquarters')) {
            Schema::create('headquarters', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->unsignedBigInteger('municipality_id');
                $table->text('address')->nullable();
                $table->string('phone', 20)->nullable();
                $table->timestamps();

                $table->index('name', 'idx_headquarters_name');

                $table->foreign('municipality_id')
                    ->references('id')->on('municipalities')
                    ->onDelete('cascade');
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
        Schema::dropIfExists('headquarters');
    }
};


