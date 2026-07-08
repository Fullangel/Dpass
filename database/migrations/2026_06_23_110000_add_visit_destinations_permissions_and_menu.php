<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            'visit_destinations',
            'visit_destinations_create',
            'visit_destinations_edit',
            'visit_destinations_delete',
            'visit_destination_queue',
        ];

        $timestamp = now();

        foreach ($permissions as $name) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $name, 'guard_name' => 'web'],
                ['created_at' => $timestamp, 'updated_at' => $timestamp]
            );
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissionIds = DB::table('permissions')
            ->whereIn('name', $permissions)
            ->pluck('id', 'name');

        $assign = function (?Role $role, array $names) use ($permissionIds): void {
            if (! $role) {
                return;
            }

            foreach ($names as $name) {
                if (! isset($permissionIds[$name])) {
                    continue;
                }

                DB::table('role_has_permissions')->updateOrInsert([
                    'permission_id' => $permissionIds[$name],
                    'role_id' => $role->id,
                ]);
            }
        };

        $assign(Role::query()->where('name', 'Admin')->first(), $permissions);
        $assign(Role::query()->where('name', 'supervisor')->first(), [
            'visit_destinations',
            'visit_destinations_edit',
            'visit_destination_queue',
        ]);

        if (DB::getSchemaBuilder()->hasTable('backend_menus')) {
            $exists = DB::table('backend_menus')->where('link', 'visit-destinations')->exists();
            if (! $exists) {
                DB::table('backend_menus')->insert([
                    'name' => 'visit_destinations',
                    'link' => 'visit-destinations',
                    'icon' => 'fas fa-route',
                    'parent_id' => 0,
                    'priority' => 8350,
                    'status' => 1,
                ]);
            }
        }
    }

    public function down(): void
    {
        $permissions = [
            'visit_destinations',
            'visit_destinations_create',
            'visit_destinations_edit',
            'visit_destinations_delete',
            'visit_destination_queue',
        ];

        $permissionIds = DB::table('permissions')->whereIn('name', $permissions)->pluck('id');

        if ($permissionIds->isNotEmpty()) {
            DB::table('role_has_permissions')->whereIn('permission_id', $permissionIds)->delete();
            DB::table('permissions')->whereIn('id', $permissionIds)->delete();
        }

        if (DB::getSchemaBuilder()->hasTable('backend_menus')) {
            DB::table('backend_menus')->where('link', 'visit-destinations')->delete();
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
