<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Designation;
use App\Models\PreRegister;

class SupervisorHeadquartersScope
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $resource = null)
    {
        $user = auth()->user();
        
        // Check if user has supervisor or reception role
        if (!$user->hasRole('supervisor') && !$user->hasRole('Reception')) {
            return $next($request);
        }

        // Get supervisor's employee record to find their headquarters
        $supervisorEmployee = Employee::where('user_id', $user->id)->first();
        
        if (!$supervisorEmployee) {
            abort(403, 'User must have an employee record assigned to a headquarters.');
        }

        $supervisorHeadquartersId = $supervisorEmployee->headquarters_id;

        // Apply headquarters scoping based on the resource type
        $scopeResponse = null;
        switch ($resource) {
            case 'employees':
                $scopeResponse = $this->scopeEmployees($request, $supervisorHeadquartersId);
                break;
            case 'departments':
                $scopeResponse = $this->scopeDepartments($request, $supervisorHeadquartersId);
                break;
            case 'designations':
                $scopeResponse = $this->scopeDesignations($request, $supervisorHeadquartersId);
                break;
            case 'pre-registers':
                $scopeResponse = $this->scopePreRegisters($request, $supervisorHeadquartersId);
                break;
            case 'visitors':
                $scopeResponse = $this->scopeVisitors($request, $supervisorHeadquartersId);
                break;
            case 'reports':
                $scopeResponse = $this->scopeReports($request, $supervisorHeadquartersId);
                break;
        }

        // Si el scope devolvió una respuesta (redirect, abort), devolverla
        if ($scopeResponse instanceof \Symfony\Component\HttpFoundation\Response) {
            return $scopeResponse;
        }

        // Store supervisor's headquarters ID in request for later use
        $request->merge(['supervisor_headquarters_id' => $supervisorHeadquartersId]);

        return $next($request);
    }

    protected function scopeEmployees($request, $headquartersId)
    {
        // For index requests, filter by headquarters
        if ($request->isMethod('GET') && !$request->route('employee')) {
            $request->merge(['headquarters_id' => $headquartersId]);
        }

        // For specific employee operations, check if employee belongs to supervisor's headquarters
        $routeEmployee = $request->route('employee');
        if (!$routeEmployee) {
            return;
        }

        // Obtener siempre una instancia única de Employee (el parámetro puede ser ID, modelo o colección por route binding)
        $employee = $this->resolveEmployeeFromRoute($routeEmployee);

        if (!$employee instanceof Employee) {
            return;
        }

        // Permitir acceso si el empleado pertenece a la sede del supervisor
        // (incluye el caso de edición para cambiar de sede: se valida el registro actual, el update se permite en el controlador)
        if ((int) $employee->headquarters_id !== (int) $headquartersId) {
            return redirect()->route('admin.employees.index')
                ->withError('No tiene permiso para acceder a este empleado.');
        }
    }

    /**
     * Resuelve una instancia de Employee desde el parámetro de ruta.
     * El parámetro puede ser: ID (int/string), modelo Employee ya resuelto, o en edge cases una colección.
     *
     * @param  mixed  $routeEmployee
     * @return \App\Models\Employee|null
     */
    protected function resolveEmployeeFromRoute($routeEmployee)
    {
        if ($routeEmployee instanceof Employee) {
            return $routeEmployee;
        }

        if ($routeEmployee instanceof \Illuminate\Support\Collection) {
            return $routeEmployee->first();
        }

        if (is_numeric($routeEmployee) || is_string($routeEmployee)) {
            return Employee::find($routeEmployee);
        }

        return null;
    }

    protected function scopeDepartments($request, $headquartersId)
    {
        // Filter departments by headquarters
        if ($request->isMethod('GET')) {
            $request->merge(['headquarters_id' => $headquartersId]);
        }
        
        // For specific department operations, check if department belongs to supervisor's headquarters
        if ($request->route('department')) {
            $departmentId = $request->route('department');
            $department = Department::find($departmentId);
            
            if ($department && $department->headquarters_id != $headquartersId) {
                abort(403, 'You can only manage departments from your assigned headquarters.');
            }
        }
    }

    protected function scopeDesignations($request, $headquartersId)
    {
        // Filter designations by headquarters
        if ($request->isMethod('GET')) {
            $request->merge(['headquarters_id' => $headquartersId]);
        }
        
        // For specific designation operations, check if designation belongs to supervisor's headquarters
        if ($request->route('designation')) {
            $designationId = $request->route('designation');
            $designation = Designation::find($designationId);
            
            if ($designation && $designation->headquarters_id != $headquartersId) {
                abort(403, 'You can only manage designations from your assigned headquarters.');
            }
        }
    }

    protected function scopePreRegisters($request, $headquartersId)
    {
        // Filter pre-registers by headquarters
        if ($request->isMethod('GET')) {
            $request->merge(['headquarters_id' => $headquartersId]);
        }
        
        // For specific pre-register operations, check if pre-register belongs to supervisor's headquarters
        if ($request->route('pre-register')) {
            $preRegisterId = $request->route('pre-register');
            $preRegister = PreRegister::find($preRegisterId);
            
            if ($preRegister && $preRegister->headquarters_id != $headquartersId) {
                abort(403, 'You can only manage pre-registers from your assigned headquarters.');
            }
        }
    }

    protected function scopeVisitors($request, $headquartersId)
    {
        // Filter visitors by headquarters
        if ($request->isMethod('GET')) {
            $request->merge(['headquarters_id' => $headquartersId]);
        }
        
        // For specific visitor operations, check if visitor belongs to supervisor's headquarters
        if ($request->route('visitor')) {
            $visitingDetail = $request->route('visitor');
            
            // Si el parámetro no es una instancia del modelo VisitingDetails (podría ser un ID o incluso un modelo Visitor si el binding es confuso)
            // Asumimos que si no es VisitingDetails, intentamos buscarlo. 
            // CUIDADO: Si es un modelo Visitor, find() podría fallar o dar resultados erróneos si se busca en VisitingDetails.
            // Pero mantendremos la lógica original de intentar buscar en VisitingDetails si no es el objeto esperado.
            
            if (!($visitingDetail instanceof \App\Models\VisitingDetails)) {
                 $visitingDetail = \App\Models\VisitingDetails::find($visitingDetail);
            }
            
            if ($visitingDetail instanceof \App\Models\VisitingDetails && $visitingDetail->headquarters_id != $headquartersId) {
                abort(403, 'No tienes permiso para acceder a este visitante.');
            }
        }
        
        // For specific visitor operations using visiting_details route parameter
        if ($request->route('visitingDetail')) {
            $visitingDetail = $request->route('visitingDetail');
            
            // Si el parámetro no es una instancia del modelo, buscarlo por ID
            if (!($visitingDetail instanceof \App\Models\VisitingDetails)) {
                $visitingDetail = \App\Models\VisitingDetails::find($visitingDetail);
            }
            
            if ($visitingDetail && $visitingDetail->headquarters_id != $headquartersId) {
                abort(403, 'No tienes permiso para acceder a este visitante.');
            }
        }
    }

    protected function scopeReports($request, $headquartersId)
    {
        // Para reportes, los supervisores solo deben ver datos de su sede
        // Esto se maneja en el controlador de reportes con filtros
        if ($request->isMethod('GET')) {
            $request->merge(['headquarters_id' => $headquartersId]);
        }
    }
}