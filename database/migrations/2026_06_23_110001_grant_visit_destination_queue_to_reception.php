<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $receptionRole = Role::query()->where('name', 'Reception')->first();
        $permissionId = DB::table('permissions')
            ->where('name', 'visit_destination_queue')
            ->where('guard_name', 'web')
            ->value('id');

        if ($receptionRole && $permissionId) {
            DB::table('role_has_permissions')->updateOrInsert([
                'permission_id' => $permissionId,
                'role_id' => $receptionRole->id,
            ]);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        $receptionRole = Role::query()->where('name', 'Reception')->first();
        $permissionId = DB::table('permissions')
            ->where('name', 'visit_destination_queue')
            ->where('guard_name', 'web')
            ->value('id');

        if ($receptionRole && $permissionId) {
            DB::table('role_has_permissions')
                ->where('permission_id', $permissionId)
                ->where('role_id', $receptionRole->id)
                ->delete();
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
