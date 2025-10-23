<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SupervisorRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create supervisor role if it doesn't exist
        $supervisorRole = Role::firstOrCreate(['name' => 'supervisor']);

        // Create specific permissions for supervisors (headquarters-scoped)
        $supervisorPermissions = [
            // Employee permissions for their headquarters only
            'employees_create_headquarters',
            'employees_edit_headquarters',
            'employees_show_headquarters',
            'employees_delete_headquarters',
            
            // Department permissions for their headquarters only
            'departments_create_headquarters',
            'departments_edit_headquarters',
            'departments_show_headquarters',
            'departments_delete_headquarters',
            
            // Designation permissions for their headquarters only
            'designations_create_headquarters',
            'designations_edit_headquarters',
            'designations_show_headquarters',
            'designations_delete_headquarters',
            
            // Pre-register permissions for their headquarters only
            'pre-registers_create_headquarters',
            'pre-registers_edit_headquarters',
            'pre-registers_show_headquarters',
            'pre-registers_delete_headquarters',
            
            // View permissions for general information (base permissions)
            'employees',
            'employees_headquarters',
            'departments',
            'departments_headquarters',
            'designations',
            'designations_headquarters',
            'pre-registers',
            'pre-registers_headquarters',
            'visitors',
            'visitors_show',
            'dashboard'
        ];

        // Create permissions if they don't exist
        foreach ($supervisorPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to supervisor role
        $supervisorRole->syncPermissions($supervisorPermissions);
    }
}