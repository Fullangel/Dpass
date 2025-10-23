<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Employee;
use App\Models\VisitingDetails;
use App\Models\Headquarters;
use App\Models\Region;
use App\Http\Services\Visitor\VisitorService;

class TestVisitorFilters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:visitor-filters';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar los filtros de visitantes por rol';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Probando filtros de visitantes...');
        
        // Crear datos de prueba si no existen
        $this->crearDatosDePrueba();
        
        // Probar con diferentes usuarios
        $this->probarConUsuarioAdmin();
        $this->probarConUsuarioSupervisor();
        $this->probarConUsuarioRecepcion();
        $this->probarConUsuarioEmpleado();
        
        return 0;
    }
    
    private function crearDatosDePrueba()
    {
        $this->info('Creando datos de prueba...');
        
        // Crear sedes y regiones si no existen
        $region = Region::firstOrCreate(['name' => 'Región Test'], ['name' => 'Región Test']);
        $headquarters = Headquarters::firstOrCreate(['name' => 'Sede Test'], ['name' => 'Sede Test', 'region_id' => $region->id]);
        
        // Crear usuarios de prueba si no existen
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            ['name' => 'Admin Test', 'password' => bcrypt('password')]
        );
        
        $supervisorUser = User::firstOrCreate(
            ['email' => 'supervisor@test.com'],
            ['name' => 'Supervisor Test', 'password' => bcrypt('password')]
        );
        
        $receptionUser = User::firstOrCreate(
            ['email' => 'reception@test.com'],
            ['name' => 'Reception Test', 'password' => bcrypt('password')]
        );
        
        $employeeUser = User::firstOrCreate(
            ['email' => 'employee@test.com'],
            ['name' => 'Employee Test', 'password' => bcrypt('password')]
        );
        
        // Crear empleados asociados
        $supervisorEmployee = Employee::firstOrCreate(
            ['user_id' => $supervisorUser->id],
            ['region_id' => $region->id, 'headquarters_id' => $headquarters->id, 'status' => 1]
        );
        
        $receptionEmployee = Employee::firstOrCreate(
            ['user_id' => $receptionUser->id],
            ['region_id' => $region->id, 'headquarters_id' => $headquarters->id, 'status' => 1]
        );
        
        $employeeEmployee = Employee::firstOrCreate(
            ['user_id' => $employeeUser->id],
            ['region_id' => $region->id, 'headquarters_id' => $headquarters->id, 'status' => 1]
        );
        
        // Crear algunas visitas de prueba
        $this->crearVisitaDePrueba($supervisorEmployee, $region, $headquarters);
        $this->crearVisitaDePrueba($receptionEmployee, $region, $headquarters);
        $this->crearVisitaDePrueba($employeeEmployee, $region, $headquarters);
        
        $this->info('Datos de prueba creados correctamente.');
    }
    
    private function crearVisitaDePrueba($employee, $region, $headquarters)
    {
        $visitor = \App\Models\Visitor::firstOrCreate(
            ['national_identification_no' => 'TEST-' . $employee->id],
            [
                'first_name' => 'Visitante',
                'last_name' => 'Test ' . $employee->id,
                'email' => 'visitante' . $employee->id . '@test.com',
                'phone' => '1234567890',
                'status' => 1
            ]
        );
        
        VisitingDetails::firstOrCreate(
            [
                'visitor_id' => $visitor->id,
                'employee_id' => $employee->id,
                'region_id' => $region->id,
                'headquarters_id' => $headquarters->id,
                'reg_no' => 'TEST-' . $employee->id
            ],
            [
                'purpose' => 'Visita de prueba',
                'company_name' => 'Empresa Test',
                'status' => 'accept',
                'user_id' => $employee->id
            ]
        );
    }
    
    private function probarConUsuarioAdmin()
    {
        $this->info('\\n=== Probando con usuario Admin ===');
        $user = User::where('email', 'admin@test.com')->first();
        if ($user) {
            \Auth::login($user);
            $visitorService = new VisitorService();
            $visitors = $visitorService->all();
            $this->info('Admin - Total de visitantes: ' . $visitors->count());
        }
    }
    
    private function probarConUsuarioSupervisor()
    {
        $this->info('\\n=== Probando con usuario Supervisor ===');
        $user = User::where('email', 'supervisor@test.com')->first();
        if ($user) {
            \Auth::login($user);
            $visitorService = new VisitorService();
            $visitors = $visitorService->all();
            $this->info('Supervisor - Total de visitantes: ' . $visitors->count());
            
            if ($user->employee && $user->employee->headquarters_id) {
                $this->info('Sede del supervisor: ' . $user->employee->headquarters_id);
                foreach ($visitors as $visitor) {
                    $this->line('- Visita ID: ' . $visitor->id . ' | Sede: ' . $visitor->headquarters_id);
                }
            }
        }
    }
    
    private function probarConUsuarioRecepcion()
    {
        $this->info('\\n=== Probando con usuario Reception ===');
        $user = User::where('email', 'reception@test.com')->first();
        if ($user) {
            \Auth::login($user);
            $visitorService = new VisitorService();
            $visitors = $visitorService->all();
            $this->info('Recepción - Total de visitantes: ' . $visitors->count());
            
            if ($user->employee && $user->employee->headquarters_id) {
                $this->info('Sede de recepción: ' . $user->employee->headquarters_id);
                foreach ($visitors as $visitor) {
                    $this->line('- Visita ID: ' . $visitor->id . ' | Sede: ' . $visitor->headquarters_id);
                }
            }
        }
    }
    
    private function probarConUsuarioEmpleado()
    {
        $this->info('\\n=== Probando con usuario Employee ===');
        $user = User::where('email', 'employee@test.com')->first();
        if ($user) {
            \Auth::login($user);
            $visitorService = new VisitorService();
            $visitors = $visitorService->all();
            $this->info('Empleado - Total de visitantes: ' . $visitors->count());
            
            if ($user->employee) {
                $this->info('ID Empleado: ' . $user->employee->id);
                foreach ($visitors as $visitor) {
                    $this->line('- Visita ID: ' . $visitor->id . ' | Empleado ID: ' . $visitor->employee_id);
                }
            }
        }
    }
}