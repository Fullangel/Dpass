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
        switch ($resource) {
            case 'employees':
                $this->scopeEmployees($request, $supervisorHeadquartersId);
                break;
            case 'departments':
                $this->scopeDepartments($request, $supervisorHeadquartersId);
                break;
            case 'designations':
                $this->scopeDesignations($request, $supervisorHeadquartersId);
                break;
            case 'pre-registers':
                $this->scopePreRegisters($request, $supervisorHeadquartersId);
                break;
            case 'visitors':
                $this->scopeVisitors($request, $supervisorHeadquartersId);
                break;
            case 'reports':
                $this->scopeReports($request, $supervisorHeadquartersId);
                break;
        }

        // Store supervisor's headquarters ID in request for later use
        $request->merge(['supervisor_headquarters_id' => $supervisorHeadquartersId]);

        return $next($request);
    }

    protected function scopeEmployees($request, $headquartersId)
    {
        // Obtener el usuario autenticado
        $user = auth()->user();

        // For index requests, filter by headquarters
        if ($request->isMethod('GET') && !$request->route('employee')) {
            $request->merge(['headquarters_id' => $headquartersId]);
        }
        
        // For specific employee operations, check if employee belongs to supervisor's headquarters
        if ($request->route('employee')) {
            $employeeId = $request->route('employee');
            $employee = Employee::find($employeeId);
            
            if ($employee && $employee->headquarters_id != $headquartersId) {
                return redirect()->route('admin.employees.index')
                    ->withError('No tiene permiso para acceder a este empleado.');
            }
        }
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
            $visitorId = $request->route('visitor');
            $visitingDetail = \App\Models\VisitingDetails::find($visitorId);
            
            if ($visitingDetail && $visitingDetail->headquarters_id != $headquartersId) {
                abort(403, 'No tienes permiso para acceder a este visitante.');
            }
        }
        
        // For specific visitor operations using visiting_details route parameter
        if ($request->route('visitingDetail')) {
            $visitingDetailId = $request->route('visitingDetail');
            $visitingDetail = \App\Models\VisitingDetails::find($visitingDetailId);
            
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