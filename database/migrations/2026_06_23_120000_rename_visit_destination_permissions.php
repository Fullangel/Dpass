<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private array $map = [
        'visit_destinations' => 'visit-destinations',
        'visit_destinations_create' => 'visit-destinations_create',
        'visit_destinations_edit' => 'visit-destinations_edit',
        'visit_destinations_delete' => 'visit-destinations_delete',
        'visit_destination_queue' => 'visit-destination-queue',
    ];

    public function up(): void
    {
        foreach ($this->map as $from => $to) {
            DB::table('permissions')
                ->where('name', $from)
                ->where('guard_name', 'web')
                ->update(['name' => $to, 'updated_at' => now()]);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        foreach ($this->map as $from => $to) {
            DB::table('permissions')
                ->where('name', $to)
                ->where('guard_name', 'web')
                ->update(['name' => $from, 'updated_at' => now()]);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
