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
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'region_id')) {
                $table->unsignedBigInteger('region_id')->nullable()->after('designation_id');
                $table->foreign('region_id')->references('id')->on('regions')->onDelete('set null');
            }
            if (!Schema::hasColumn('employees', 'headquarters_id')) {
                $table->unsignedBigInteger('headquarters_id')->nullable()->after('region_id');
                $table->foreign('headquarters_id')->references('id')->on('headquarters')->onDelete('set null');
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
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'headquarters_id')) {
                $table->dropForeign(['headquarters_id']);
                $table->dropColumn('headquarters_id');
            }
            if (Schema::hasColumn('employees', 'region_id')) {
                $table->dropForeign(['region_id']);
                $table->dropColumn('region_id');
            }
        });
    }
};
