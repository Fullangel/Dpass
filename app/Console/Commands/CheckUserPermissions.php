<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CheckUserPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:user-permissions {user_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar los permisos de un usuario específico';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        
        $user = User::find($userId);
        
        if (!$user) {
            $this->error("Usuario con ID {$userId} no encontrado.");
            return 1;
        }

        $this->info("Usuario: {$user->name} ({$user->email})");
        $this->info("Roles: " . $user->roles->pluck('name')->implode(', '));
        $this->info("=");
        
        $permissions = [
            'visitors',
            'visitors_create', 
            'visitors_edit',
            'visitors_delete',
            'visitors_show'
        ];
        
        $this->info("Permisos de visitantes:");
        foreach ($permissions as $permission) {
            $hasPermission = $user->hasPermissionTo($permission);
            $status = $hasPermission ? '✅' : '❌';
            $this->info("{$status} {$permission}");
        }
        
        $this->info("=");
        $this->info("¿Puede ver visitantes? " . ($user->hasPermissionTo('visitors') || $user->hasPermissionTo('visitors_show') ? 'SÍ' : 'NO'));
        
        // Verificar información de empleado y sede
        if ($user->employee) {
            $this->info("=");
            $this->info("Información de empleado:");
            $this->info("- Empleado ID: {$user->employee->id}");
            $this->info("- Región: " . ($user->employee->region->name ?? 'Sin región'));
            $this->info("- Sede: " . ($user->employee->headquarters->name ?? 'Sin sede'));
        } else {
            $this->info("=");
            $this->warn("Este usuario no tiene un empleado asociado.");
        }
        
        return 0;
    }
}