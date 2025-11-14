<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function foreignKeyExists(string $table, string $constraint): bool
    {
        $connection = config('database.default');
        $dbName = config('database.connections.' . $connection . '.database');
        
        // Consulta compatible con ambos motores de base de datos
        if (config('database.connections.' . $connection . '.driver') === 'pgsql') {
            $result = \Illuminate\Support\Facades\DB::select(
                'SELECT constraint_name FROM information_schema.table_constraints 
                 WHERE table_catalog = ? AND table_name = ? AND constraint_name = ? AND constraint_type = ?',
                [$dbName, $table, $constraint, 'FOREIGN KEY']
            );
        } else {
            // MySQL
            $result = \Illuminate\Support\Facades\DB::select(
                'SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS 
                 WHERE CONSTRAINT_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = "FOREIGN KEY"',
                [$dbName, $table, $constraint]
            );
        }
        
        return !empty($result);
    }
    public function up()
    {
        // departments.headquarters_id
        if (Schema::hasTable('departments') && !Schema::hasColumn('departments', 'headquarters_id')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->unsignedBigInteger('headquarters_id')->nullable()->after('status');
                $table->foreign('headquarters_id')
                    ->references('id')->on('headquarters')
                    ->onDelete('set null');
            });
        }

        // employees.user_id, department_id, designation_id
        if (Schema::hasTable('employees')) {
            Schema::table('employees', function (Blueprint $table) {
                if (!Schema::hasColumn('employees', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->after('status');
                }
                // Add FKs only if not present
                if (!$this->foreignKeyExists('employees', 'employees_user_id_foreign')) {
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                }
                if (!$this->foreignKeyExists('employees', 'employees_department_id_foreign')) {
                    $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
                }
                if (!$this->foreignKeyExists('employees', 'employees_designation_id_foreign')) {
                    $table->foreign('designation_id')->references('id')->on('designations')->onDelete('cascade');
                }
            });
        }

        // visiting_details add headquarters_id and FKs
        if (Schema::hasTable('visiting_details') && !Schema::hasColumn('visiting_details', 'headquarters_id')) {
            Schema::table('visiting_details', function (Blueprint $table) {
                $table->unsignedBigInteger('headquarters_id')->nullable()->after('visitor_id');
            });
        }
        if (Schema::hasTable('visiting_details')) {
            Schema::table('visiting_details', function (Blueprint $table) {
                if (!$this->foreignKeyExists('visiting_details', 'visiting_details_user_id_foreign')) {
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                }
                if (!$this->foreignKeyExists('visiting_details', 'visiting_details_employee_id_foreign')) {
                    $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
                }
                if (!$this->foreignKeyExists('visiting_details', 'visiting_details_visitor_id_foreign')) {
                    $table->foreign('visitor_id')->references('id')->on('visitors')->onDelete('cascade');
                }
                if (!$this->foreignKeyExists('visiting_details', 'visiting_details_headquarters_id_foreign')) {
                    $table->foreign('headquarters_id')->references('id')->on('headquarters')->onDelete('set null');
                }
            });
        }

        // pre_registers FKs (ensure correct column types first)
        if (Schema::hasTable('pre_registers')) {
            // Ensure visitor_id is BIGINT UNSIGNED to match visitors.id
            if (Schema::hasColumn('pre_registers', 'visitor_id')) {
                // Usar Schema en lugar de SQL crudo para compatibilidad cross-DB
                Schema::table('pre_registers', function (Blueprint $table) {
                    $table->unsignedBigInteger('visitor_id')->change();
                });
            }
            if (Schema::hasColumn('pre_registers', 'employee_id')) {
                Schema::table('pre_registers', function (Blueprint $table) {
                    $table->unsignedBigInteger('employee_id')->change();
                });
            }
            Schema::table('pre_registers', function (Blueprint $table) {
                if (!$this->foreignKeyExists('pre_registers', 'pre_registers_employee_id_foreign')) {
                    $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
                }
                if (!$this->foreignKeyExists('pre_registers', 'pre_registers_visitor_id_foreign')) {
                    $table->foreign('visitor_id')->references('id')->on('visitors')->onDelete('cascade');
                }
            });
        }

        // invitations FKs
        if (Schema::hasTable('invitations')) {
            Schema::table('invitations', function (Blueprint $table) {
                if (!$this->foreignKeyExists('invitations', 'invitations_visitor_id_foreign')) {
                    $table->foreign('visitor_id')->references('id')->on('visitors')->onDelete('set null');
                }
            });
        }

        // attendances add headquarters_id and visitor_id if needed
        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                if (!Schema::hasColumn('attendances', 'visitor_id')) {
                    $table->unsignedBigInteger('visitor_id')->nullable()->after('user_id');
                }
                if (!Schema::hasColumn('attendances', 'headquarters_id')) {
                    $table->unsignedBigInteger('headquarters_id')->nullable()->after('visitor_id');
                }
                if (!$this->foreignKeyExists('attendances', 'attendances_user_id_foreign')) {
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                }
                if (!$this->foreignKeyExists('attendances', 'attendances_visitor_id_foreign')) {
                    $table->foreign('visitor_id')->references('id')->on('visitors')->onDelete('set null');
                }
                if (!$this->foreignKeyExists('attendances', 'attendances_headquarters_id_foreign')) {
                    $table->foreign('headquarters_id')->references('id')->on('headquarters')->onDelete('set null');
                }
            });
        }

        // bookings add headquarters_id if needed and FKs
        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                if (!Schema::hasColumn('bookings', 'headquarters_id')) {
                    $table->unsignedBigInteger('headquarters_id')->nullable()->after('employee_id');
                }
                if (!$this->foreignKeyExists('bookings', 'bookings_user_id_foreign')) {
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                }
                if (!$this->foreignKeyExists('bookings', 'bookings_employee_id_foreign')) {
                    $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
                }
                if (!$this->foreignKeyExists('bookings', 'bookings_headquarters_id_foreign')) {
                    $table->foreign('headquarters_id')->references('id')->on('headquarters')->onDelete('set null');
                }
            });
        }
    }

    public function down()
    {
        // Drop added foreign keys/columns safely
        if (Schema::hasTable('departments') && Schema::hasColumn('departments', 'headquarters_id')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->dropForeign(['headquarters_id']);
                $table->dropColumn('headquarters_id');
            });
        }

        if (Schema::hasTable('employees')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropForeign(['department_id']);
                $table->dropForeign(['designation_id']);
            });
        }

        if (Schema::hasTable('visiting_details')) {
            Schema::table('visiting_details', function (Blueprint $table) {
                if (Schema::hasColumn('visiting_details', 'headquarters_id')) {
                    $table->dropForeign(['headquarters_id']);
                    $table->dropColumn('headquarters_id');
                }
                $table->dropForeign(['user_id']);
                $table->dropForeign(['employee_id']);
                $table->dropForeign(['visitor_id']);
            });
        }

        if (Schema::hasTable('pre_registers')) {
            Schema::table('pre_registers', function (Blueprint $table) {
                $table->dropForeign(['employee_id']);
                $table->dropForeign(['visitor_id']);
            });
        }

        if (Schema::hasTable('invitations')) {
            Schema::table('invitations', function (Blueprint $table) {
                $table->dropForeign(['visitor_id']);
            });
        }

        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                if (Schema::hasColumn('attendances', 'headquarters_id')) {
                    $table->dropForeign(['headquarters_id']);
                    $table->dropColumn('headquarters_id');
                }
                if (Schema::hasColumn('attendances', 'visitor_id')) {
                    $table->dropForeign(['visitor_id']);
                    $table->dropColumn('visitor_id');
                }
                $table->dropForeign(['user_id']);
            });
        }

        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                if (Schema::hasColumn('bookings', 'headquarters_id')) {
                    $table->dropForeign(['headquarters_id']);
                    $table->dropColumn('headquarters_id');
                }
                $table->dropForeign(['user_id']);
                $table->dropForeign(['employee_id']);
            });
        }
    }
};


