<?php

namespace App\Http\Controllers\Admin;

use Setting;
use App\Enums\Status;
use App\Models\Visitor;
use App\Models\Employee;
use App\Models\PreRegister;
use App\Models\Headquarters;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\PreRegisterRequest;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Http\Services\PreRegister\PreRegisterService;
use App\Http\Controllers\BackendController;
use Illuminate\Support\Facades\Auth;

class PreRegisterController extends BackendController
{
    protected $preRegisterService;

    public function __construct(PreRegisterService $preRegisterService)
    {
        $this->preRegisterService = $preRegisterService;

        $this->middleware('auth');
        $this->data['sitetitle'] = 'Pre-registers';
        $this->middleware(['permission:pre-registers|pre-registers_headquarters'])->only('index');
        $this->middleware(['permission:pre-registers_create|pre-registers_create_headquarters'])->only('create', 'store');
        $this->middleware(['permission:pre-registers_edit|pre-registers_edit_headquarters'])->only('edit', 'update');
        $this->middleware(['permission:pre-registers_delete|pre-registers_delete_headquarters'])->only('destroy');
        $this->middleware(['permission:pre-registers_show|pre-registers_show_headquarters'])->only('show');

    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {

        return view('admin.pre-register.index');
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        
        if(auth()->user()->getrole->name == 'Employee') {
            $this->data['employees'] = Employee::where(['status'=>Status::ACTIVE,'id'=>auth()->user()->employee->id])->get();
        } else if ($user->hasRole('supervisor')) {
            $this->data['employees'] = Employee::where('status', Status::ACTIVE)
                ->where('headquarters_id', $user->headquarters_id)
                ->get();
        } else {
            $this->data['employees'] = Employee::where('status', Status::ACTIVE)->get();
        }

        return view('admin.pre-register.create', $this->data);
    }

    public function store(PreRegisterRequest $request)
    {
        $preRegister = $this->preRegisterService->make($request);

        if (setting('whatsapp_message')) {
            return redirect()->route('admin.pre-registers.show',$preRegister->id);
        }

        return redirect()->route('admin.pre-registers.index')->withSuccess('The data inserted successfully!');
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($id)
    {
        $this->data['preregister'] = $this->preRegisterService->find($id);
        if($this->data['preregister']){
            return view('admin.pre-register.show', $this->data);
        }else {
            return redirect()->route('admin.pre-registers.index')->withError('No tiene permiso para ver este pre-registro.');
        }
    }

    public function edit($id)
    {
        $user = Auth::user();
        
        if(auth()->user()->getrole->name == 'Employee') {
            $this->data['employees'] = Employee::where(['status'=>Status::ACTIVE,'id'=>auth()->user()->employee->id])->get();
        } else if ($user->hasRole('supervisor')) {
            $this->data['employees'] = Employee::where('status', Status::ACTIVE)
                ->where('headquarters_id', $user->headquarters_id)
                ->get();
        } else {
            $this->data['employees'] = Employee::where('status', Status::ACTIVE)->get();
        }
        $this->data['preregister'] = $this->preRegisterService->find($id);
        if($this->data['preregister']){
            return view('admin.pre-register.edit', $this->data);
        }else {
            return redirect()->route('admin.pre-registers.index')->withError('No tiene permiso para editar este pre-registro.');
        }
    }

    public function update(Request $request,PreRegister $preRegister)
    {
        // Verificar que el supervisor solo pueda actualizar pre-registros de su sede
        if (auth()->user()->hasRole('supervisor')) {
            if ($preRegister->headquarters_id != auth()->user()->headquarters_id) {
                return redirect()->route('admin.pre-registers.index')->withError('No tiene permiso para actualizar este pre-registro.');
            }
        }
        
        $this->preRegisterService->update($request,$preRegister->id);
        return redirect()->route('admin.pre-registers.index')->withSuccess('The data updated successfully!');
    }

    public function destroy($id)
    {
        // Verificar que el supervisor solo pueda eliminar pre-registros de su sede
        if (auth()->user()->hasRole('supervisor')) {
            $preRegister = PreRegister::find($id);
            if (!$preRegister || $preRegister->headquarters_id != auth()->user()->headquarters_id) {
                return redirect()->route('admin.pre-registers.index')->withError('No tiene permiso para eliminar este pre-registro.');
            }
        }
        
        $this->preRegisterService->delete($id);
        return redirect()->route('admin.pre-registers.index')->withSuccess('The data delete successfully!');
    }


    public function getPreRegister(Request $request)
    {
        $pre_registers = $this->preRegisterService->all($request);
        $i            = 1;
        $pre_registerArray = [];
        if (!blank($pre_registers)) {
            foreach ($pre_registers as $pre_register) {
                $pre_registerArray[$i]          = $pre_register;
                $pre_registerArray[$i]['setID'] = $i;
                $i++;
            }
        }
        return Datatables::of($pre_registerArray)
            ->addColumn('action', function ($pre_register) {
                $retAction ='';

                if(auth()->user()->can('pre-registers_show')) {
                    $retAction .= '<a href="' . route('admin.pre-registers.show', $pre_register) . '" class="btn btn-sm btn-icon mr-2  float-left btn-info" data-toggle="tooltip" data-placement="top" title="View"><i class="far fa-eye"></i></a>';
                }

                if(auth()->user()->can('pre-registers_edit')) {
                    $retAction .= '<a href="' . route('admin.pre-registers.edit', $pre_register) . '" class="btn btn-sm btn-icon float-left btn-primary" data-toggle="tooltip" data-placement="top" title="Edit"> <i class="far fa-edit"></i></a>';
                }


                if(auth()->user()->can('pre-registers_delete')) {
                    $retAction .= '<form class="float-left pl-2" action="' . route('admin.pre-registers.destroy', $pre_register). '" method="POST">' . method_field('DELETE') . csrf_field() . '<button class="btn btn-sm btn-icon btn-danger" onclick="return confirmDelete()" data-toggle="tooltip" data-placement="top" title="Delete"> <i class="fa fa-trash"></i></button></form>';
                }

                return $retAction;
            })

            ->editColumn('name', function ($pre_register) {
                return Str::limit(optional($pre_register->visitor)->name, 50);
            })
            ->editColumn('email', function ($pre_register) {
                return Str::limit(optional($pre_register->visitor)->email, 50);
            })
            ->editColumn('phone', function ($pre_register) {
                return Str::limit(optional($pre_register->visitor)->phone, 50);
            })
            ->editColumn('employee_id', function ($pre_register) {
                return optional($pre_register->employee->user)->name;
            })
            ->editColumn('expected_date', function ($pre_register) {
                if (optional($pre_register->visitor)->is_pre_register==1){
                    $date = '<p class="text-danger">' . $pre_register->expected_date . '</p>';
                }else{
                    $date = '<p>' . $pre_register->expected_date . '</p>';
                }
                return $date;
            })
            ->editColumn('expected_time', function ($pre_register) {
                if (optional($pre_register->visitor)->is_pre_register==1) {
                    $time = '<p class="text-danger">' . date('h:i A', strtotime($pre_register->expected_time)) . '</p>';
                }else {
                    $time = '<p>' . date('h:i A', strtotime($pre_register->expected_time)) . '</p>';
                }
                return $time;
            })
            ->editColumn('id', function ($pre_register) {
                return $pre_register->setID;
            })
            ->rawColumns(['name', 'action'])
            ->escapeColumns([])
            ->make(true);
    }
}
