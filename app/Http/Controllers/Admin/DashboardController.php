<?php

namespace App\Http\Controllers\Admin;

use App\Enums\VisitorStatus;
use App\Http\Controllers\BackendController;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\PreRegister;
use App\Models\VisitingDetails;
use App\Models\Visitor;


class DashboardController extends BackendController
{
    public function __construct()
    {
        parent::__construct();
        $this->data['sitetitle'] = 'Dashboard';
        $this->middleware(['permission:dashboard'])->only('index');
    }
    public function index()
    {
        if (auth()->user()->getrole->name == 'Employee') {
            $visitors       = VisitingDetails::where(['employee_id' => auth()->user()->employee->id])->orderBy('id', 'desc')->get();
            
            $visitors_check_in = VisitingDetails::where(['employee_id' => auth()->user()->employee->id])
                                                  ->where('checkin_at', '=', NULL)
                                                  ->where('checkout_at','=', NULL)
                                                  ->count();
                                                  
            $visitors_check_out = VisitingDetails::where(['employee_id' => auth()->user()->employee->id])
                                                  ->where('checkin_at', '!=', NULL)
                                                  ->where('checkout_at','!=', NULL)
                                                  ->count();
                                                  
            $visitors_in = VisitingDetails::where(['employee_id' => auth()->user()->employee->id])
                                                  ->where('status', '=', 2)
                                                  ->count();                                                  
            
            $preregister    = PreRegister::where(['employee_id' => auth()->user()->employee->id])->orderBy('id', 'desc')->get();
            $totalEmployees = 0;
        } else {
            $visitors       = VisitingDetails::orderBy('id', 'desc')->get();
            $preregister    = PreRegister::orderBy('id', 'desc')->get();
            $employees      = Employee::orderBy('id', 'desc')->get();
            
            $visitors_check_in = VisitingDetails::where('checkin_at', '=', NULL)
                                                  ->where('checkout_at','=', NULL)
                                                  ->count();
                                                  
            $visitors_check_out = VisitingDetails::where('checkin_at', '!=', NULL)
                                                  ->where('checkout_at','!=', NULL)
                                                  ->count();
                                                  
            $visitors_in = VisitingDetails::where('status', '=', 2)
                                                  ->where('checkout_at','=', NULL)
                                                  ->count();                                                  

            $totalEmployees = count($employees);
        }
        
        $totalVisitor   = count($visitors);
        $totalPrerigister = count($preregister);
        
        $attendance = Attendance::where(['user_id' => auth()->user()->id, 'date' => date('Y-m-d')])->first();
        $this->data['attendance']    = $attendance;
        $this->data['totalVisitor']    = $totalVisitor;
        $this->data['totalEmployees'] = $totalEmployees;
        $this->data['totalPrerigister']     = $totalPrerigister;
        $this->data['visitors']  = $visitors;
        $this->data['visitors_in']  = $visitors_in;
        $this->data['visitors_out']  = $visitors_check_out;
        $this->data['visitors_standby']  = $visitors_check_in;
        //dd($this->data);
        return view('admin.dashboard.index', $this->data);
    }
}
