<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('visit_destinations')) {
            Schema::create('visit_destinations', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->unsignedBigInteger('headquarters_id')->nullable();
                $table->unsignedTinyInteger('status')->default(5);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('headquarters_id')
                    ->references('id')
                    ->on('headquarters')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasTable('visit_destination_rules')) {
            Schema::create('visit_destination_rules', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('visit_destination_id');
                $table->string('rule_type', 32);
                $table->string('rule_value', 64);
                $table->unsignedTinyInteger('status')->default(5);
                $table->timestamps();

                $table->foreign('visit_destination_id')
                    ->references('id')
                    ->on('visit_destinations')
                    ->cascadeOnDelete();

                $table->unique(['visit_destination_id', 'rule_type', 'rule_value'], 'visit_destination_rules_unique');
            });
        }

        if (! Schema::hasTable('user_visit_destinations')) {
            Schema::create('user_visit_destinations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('visit_destination_id');
                $table->timestamps();

                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->cascadeOnDelete();

                $table->foreign('visit_destination_id')
                    ->references('id')
                    ->on('visit_destinations')
                    ->cascadeOnDelete();

                $table->unique(['user_id', 'visit_destination_id'], 'user_visit_destinations_unique');
            });
        }

        if (Schema::hasTable('visiting_details') && ! Schema::hasColumn('visiting_details', 'visit_destination_id')) {
            Schema::table('visiting_details', function (Blueprint $table) {
                $table->unsignedBigInteger('visit_destination_id')->nullable()->after('headquarters_id');

                $table->foreign('visit_destination_id')
                    ->references('id')
                    ->on('visit_destinations')
                    ->nullOnDelete();

                $table->index('visit_destination_id', 'visiting_details_visit_destination_id_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('visiting_details') && Schema::hasColumn('visiting_details', 'visit_destination_id')) {
            Schema::table('visiting_details', function (Blueprint $table) {
                $table->dropForeign(['visit_destination_id']);
                $table->dropIndex('visiting_details_visit_destination_id_index');
                $table->dropColumn('visit_destination_id');
            });
        }

        Schema::dropIfExists('user_visit_destinations');
        Schema::dropIfExists('visit_destination_rules');
        Schema::dropIfExists('visit_destinations');
    }
};
