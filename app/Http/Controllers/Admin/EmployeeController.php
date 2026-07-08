<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\BookingDateRequest;
use App\Http\Requests\EmployeeChekinRequest;
use App\Http\Requests\EmployeeRequest;
use App\Http\Requests\EmployeeUpdateRequest;
use App\Models\Attendance;
use App\Models\Booking;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Headquarters;
use App\Models\Invitation;
use App\Models\PreRegister;
use App\Models\Region;
use App\Models\VisitingDetails;
use App\Models\Visitor;
use App\Notifications\SendInvitationToVisitors;
use App\Http\Services\Booking\BookingService;
use App\Http\Services\Employee\EmployeeService;
use App\Services\VisitDestinationService;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Yajra\Datatables\Datatables;

class EmployeeController extends Controller
{
    protected $employeeService;
    protected $bookingService;

    public function __construct(EmployeeService $employeeService, BookingService $bookingService)
    {
        $this->employeeService = $employeeService;
        $this->bookingService = $bookingService;
        $this->middleware('auth');
        $this->data['sitetitle'] = 'Employees';

        $this->middleware(['permission:employees|employees_headquarters'])->only('index');
        $this->middleware(['permission:employees_create|employees_create_headquarters'])->only('create', 'store');
        $this->middleware(['permission:employees_edit|employees_edit_headquarters'])->only('edit', 'update');
        $this->middleware(['permission:employees_delete|employees_delete_headquarters'])->only('destroy');
        $this->middleware(['permission:employees_show|employees_show_headquarters'])->only('show');
    }

    /**
     * Get supervisor's headquarters ID
     */
    protected function getSupervisorHeadquartersId()
    {
        if (auth()->user()->hasRole('supervisor')) {
            $supervisorEmployee = Employee::where('user_id', auth()->user()->id)->first();
            return $supervisorEmployee ? $supervisorEmployee->headquarters_id : null;
        }
        return null;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $employees = $this->employeeService->all();
        $this->data['employees'] = $employees;
        return view('admin.employee.index', $this->data);
    }

    public function create(Request $request)
    {
        $this->data['designations'] = Designation::where('status', Status::ACTIVE)->get();
        $this->data['departments'] = Department::where('status', Status::ACTIVE)->get();
        $this->data['regions'] = Region::all();
        
        // Si es supervisor, solo mostrar su sede asignada y obtener datos del supervisor
        if (auth()->user()->hasRole('supervisor')) {
            $supervisorHeadquartersId = $this->getSupervisorHeadquartersId();
            $this->data['headquarters'] = Headquarters::where('id', $supervisorHeadquartersId)->get();
            
            // Obtener datos del supervisor para pre-llenar región y sede
            $supervisorEmployee = Employee::where('user_id', auth()->user()->id)->first();
            $this->data['supervisor_region_id'] = $supervisorEmployee ? $supervisorEmployee->region_id : null;
            $this->data['supervisor_headquarters_id'] = $supervisorEmployee ? $supervisorEmployee->headquarters_id : null;
            $this->data['is_supervisor'] = true;
        } else {
            $this->data['headquarters'] = Headquarters::all();
            $this->data['is_supervisor'] = false;
            $this->data['supervisor_region_id'] = null;
            $this->data['supervisor_headquarters_id'] = null;
        }

        // Si es admin o supervisor, mostrar roles disponibles (excluyendo el rol admin)
        $canAssignRole = auth()->user()->hasRole('Admin') || auth()->user()->hasRole('supervisor');
        if ($canAssignRole) {
            $this->data['roles'] = \Spatie\Permission\Models\Role::where('name', '!=', 'Admin')->get();
            $this->data['can_assign_role'] = true;
        } else {
            $this->data['roles'] = collect();
            $this->data['can_assign_role'] = false;
        }
        $this->data['is_admin'] = auth()->user()->hasRole('Admin');
        $this->data['visitDestinations'] = app(VisitDestinationService::class)
            ->destinationsForEmployeeForm($this->data['supervisor_headquarters_id'] ?? null);

        return view('admin.employee.create', $this->data);
    }

    public function store(EmployeeRequest $request)
    {
        // Validar que el supervisor solo pueda crear empleados en su sede
        if (auth()->user()->hasRole('supervisor')) {
            $supervisorHeadquartersId = $this->getSupervisorHeadquartersId();
            if ($request->headquarters_id != $supervisorHeadquartersId) {
                return redirect()->back()->withErrors(['headquarters_id' => 'No tiene permiso para crear empleados en esta sede.'])->withInput();
            }
        }

        // Validar rol: admin y supervisor pueden asignar rol (excluyendo Admin)
        if (auth()->user()->hasRole('Admin') || auth()->user()->hasRole('supervisor')) {
            $adminRoleId = \Spatie\Permission\Models\Role::where('name', 'Admin')->first()->id ?? 0;
            $request->validate([
                'role_id' => 'required|exists:roles,id|not_in:' . $adminRoleId
            ]);
        }

        $this->employeeService->make($request);
        return redirect()->route('admin.employees.index')->withSuccess('The data inserted successfully!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return void
     */
    public function show($id)
    {
        $this->data['employee'] = $this->employeeService->find($id);
        
        // Verificar que el supervisor solo pueda ver empleados de su sede
        if (auth()->user()->hasRole('supervisor')) {
            $supervisorHeadquartersId = $this->getSupervisorHeadquartersId();
            if ($this->data['employee']->headquarters_id != $supervisorHeadquartersId) {
                return redirect()->route('admin.employees.index')->withError('No tiene permiso para ver este empleado.');
            }
        }
        
        return view('admin.employee.show', $this->data);
    }

    public function edit($id)
    {
        $this->data['employee'] = $this->employeeService->find($id);
        
        // Verificar que el supervisor solo pueda editar empleados de su sede
        if (auth()->user()->hasRole('supervisor')) {
            $supervisorHeadquartersId = $this->getSupervisorHeadquartersId();
            if ($this->data['employee']->headquarters_id != $supervisorHeadquartersId) {
                return redirect()->route('admin.employees.index')->withError('No tiene permiso para editar este empleado.');
            }
        }
        
        $this->data['designations'] = Designation::where('status', Status::ACTIVE)->get();
        $this->data['departments'] = Department::where('status', Status::ACTIVE)->get();
        $this->data['regions'] = Region::all();
        
        // Si es supervisor, solo mostrar su sede asignada
        if (auth()->user()->hasRole('supervisor')) {
            $supervisorHeadquartersId = $this->getSupervisorHeadquartersId();
            $this->data['headquarters'] = Headquarters::where('id', $supervisorHeadquartersId)->get();
        } else {
            $this->data['headquarters'] = Headquarters::all();
        }

        // Si es admin o supervisor, mostrar roles disponibles (excluyendo el rol admin)
        $canAssignRole = auth()->user()->hasRole('Admin') || auth()->user()->hasRole('supervisor');
        if ($canAssignRole) {
            $this->data['roles'] = \Spatie\Permission\Models\Role::where('name', '!=', 'Admin')->get();
            $this->data['can_assign_role'] = true;
            // Obtener el rol actual del empleado
            $employeeRole = $this->data['employee']->user->roles->first();
            $this->data['employee_role_id'] = $employeeRole ? $employeeRole->id : null;
        } else {
            $this->data['roles'] = collect();
            $this->data['can_assign_role'] = false;
        }
        $this->data['is_admin'] = auth()->user()->hasRole('Admin');
        $this->data['visitDestinations'] = app(VisitDestinationService::class)
            ->destinationsForEmployeeForm($this->data['employee']->headquarters_id);
        $this->data['assignedVisitDestinationIds'] = $this->data['employee']->user
            ? $this->data['employee']->user->visitDestinations()->pluck('visit_destinations.id')->all()
            : [];
        
        return view('admin.employee.edit', $this->data);
    }
    public function update(EmployeeUpdateRequest $request, Employee $employee)
    {
        // El supervisor solo puede editar empleados que pertenecen a su sede (validado por middleware).
        // Se permite cambiar la sede del empleado (transferencia): el middleware valida el registro actual al acceder a edit/update.

        // Validar rol: admin y supervisor pueden actualizar rol (excluyendo Admin)
        if (auth()->user()->hasRole('Admin') || auth()->user()->hasRole('supervisor')) {
            $adminRoleId = \Spatie\Permission\Models\Role::where('name', 'Admin')->first()->id ?? 0;
            $request->validate([
                'role_id' => 'required|exists:roles,id|not_in:' . $adminRoleId
            ]);
        }
        
        $this->employeeService->update($employee->id, $request);
        return redirect()->route('admin.employees.index')->withSuccess('The data updated successfully!');
    }


    public function checkEmployee(EmployeeChekinRequest $request, $id)
    {
        $this->employeeService->check($id, $request);
        return back()->with(['success' => 'Employee Checkin updated successfully.']);
    }

    public function destroy($id)
    {
        // Verificar que el supervisor solo pueda eliminar empleados de su sede
        if (auth()->user()->hasRole('supervisor')) {
            $employee = Employee::find($id);
            if (!$employee || $employee->headquarters_id != $this->getSupervisorHeadquartersId()) {
                return redirect()->route('admin.employees.index')->withError('No tiene permiso para eliminar este empleado.');
            }
        }
        
        $this->employeeService->delete($id);
        return redirect()->route('admin.employees.index')->with(['success' => 'Employee delete successfully.']);
    }


    public function getEmployees(Request $request)
    {
        // Construimos un query Eloquent optimizado y dejamos el paginado/filtrado a DataTables.
        $user = auth()->user();

        $query = Employee::query()
            ->with(['user', 'region', 'headquarters'])
            ->orderByDesc('id');

        // Filtro por sede para supervisores (equivalente a EmployeeService::all()).
        if ($user && $user->hasRole('supervisor')) {
            $supervisorEmployee = Employee::where('user_id', $user->id)->first();
            if ($supervisorEmployee) {
                $query->where('headquarters_id', $supervisorEmployee->headquarters_id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // Si viene un status concreto desde el front, filtrarlo.
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $likeOperator = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        return Datatables::of($query)
            ->addIndexColumn()
            ->filterColumn('name', function ($query, $keyword) use ($likeOperator) {
                $term = '%' . addcslashes((string) $keyword, '%_\\') . '%';
                $query->where(function ($q) use ($term, $likeOperator) {
                    $q->where('first_name', $likeOperator, $term)
                        ->orWhere('last_name', $likeOperator, $term)
                        ->orWhereHas('user', function ($uq) use ($term, $likeOperator) {
                            $uq->where('first_name', $likeOperator, $term)
                                ->orWhere('last_name', $likeOperator, $term);
                        });
                });
            })
            ->filterColumn('email', function ($query, $keyword) use ($likeOperator) {
                $term = '%' . addcslashes((string) $keyword, '%_\\') . '%';
                $query->whereHas('user', function ($q) use ($term, $likeOperator) {
                    $q->where('email', $likeOperator, $term);
                });
            })
            ->filterColumn('phone', function ($query, $keyword) use ($likeOperator) {
                $term = '%' . addcslashes((string) $keyword, '%_\\') . '%';
                $query->where(function ($q) use ($term, $likeOperator) {
                    $q->where('phone', $likeOperator, $term)
                        ->orWhereHas('user', function ($uq) use ($term, $likeOperator) {
                            $uq->where('phone', $likeOperator, $term);
                        });
                });
            })
            ->filterColumn('region', function ($query, $keyword) use ($likeOperator) {
                $term = '%' . addcslashes((string) $keyword, '%_\\') . '%';
                $query->whereHas('region', function ($q) use ($term, $likeOperator) {
                    $q->where('name', $likeOperator, $term);
                });
            })
            ->filterColumn('headquarters', function ($query, $keyword) use ($likeOperator) {
                $term = '%' . addcslashes((string) $keyword, '%_\\') . '%';
                $query->whereHas('headquarters', function ($q) use ($term, $likeOperator) {
                    $q->where('name', $likeOperator, $term);
                });
            })
            ->filterColumn('date_of_joining', function ($query, $keyword) use ($likeOperator) {
                $term = '%' . addcslashes((string) $keyword, '%_\\') . '%';
                $query->where('date_of_joining', $likeOperator, $term);
            })
            ->filterColumn('status', function ($query, $keyword) use ($likeOperator) {
                $term = mb_strtolower(trim((string) $keyword), 'UTF-8');

                if ($term !== '') {
                    if (str_contains($term, 'act')) {
                        $query->where('status', Status::ACTIVE);
                        return;
                    }
                    if (str_contains($term, 'inact')) {
                        $query->where('status', Status::INACTIVE);
                        return;
                    }
                }

                $search = '%' . addcslashes((string) $keyword, '%_\\') . '%';
                $query->whereRaw('CAST(status AS TEXT) ' . $likeOperator . ' ?', [$search]);
            })
            ->addColumn('action', function ($employee) {
                $retAction = '';

                if (auth()->user()->can('employees_show') || auth()->user()->can('employees_show_headquarters')) {
                    $retAction .= '<a href="' . route('admin.employees.show', $employee) . '" class="btn btn-sm btn-icon mr-2  float-left btn-info" data-toggle="tooltip" data-placement="top" title="View"><i class="far fa-eye"></i></a>';
                }

                if (auth()->user()->can('employees_edit') || auth()->user()->can('employees_edit_headquarters')) {
                    $retAction .= '<a href="' . route('admin.employees.edit', $employee) . '" class="btn btn-sm btn-icon float-left btn-primary" data-toggle="tooltip" data-placement="top" title="Edit"> <i class="far fa-edit"></i></a>';
                }

                if (auth()->user()->can('employees_delete') || auth()->user()->can('employees_delete_headquarters')) {
                    $retAction .= '<form class="float-left pl-2" action="' . route('admin.employees.destroy', $employee) . '" method="POST">' . method_field('DELETE') . csrf_field() . '<button class="btn btn-sm btn-icon btn-danger" data-toggle="tooltip" data-placement="top" title="Delete"> <i class="fa fa-trash"></i></button></form>';
                }

                return $retAction;
            })
            ->addColumn('image', function ($employee) {
                return '<figure class="avatar mr-2"><img src="' . $employee->user->images . '" alt=""></figure>';
            })
            ->editColumn('name', function ($employee) {
                return Str::limit($employee->name, 50);
            })
            ->editColumn('email', function ($employee) {
                return Str::limit(optional($employee->user)->email, 50);
            })
            ->editColumn('phone', function ($employee) {
                return Str::limit(optional($employee->user)->phone, 50);
            })
            ->editColumn('region', function ($employee) {
                return optional($employee->region)->name ?? '-';
            })
            ->editColumn('headquarters', function ($employee) {
                return optional($employee->headquarters)->name ?? '-';
            })
            ->editColumn('status', function ($employee) {
                return ($employee->status == 5 ? trans('statuses.' . Status::ACTIVE) : trans('statuses.' . Status::INACTIVE));
            })
            ->editColumn('date_of_joining', function ($employee) {
                return $employee->date_of_joining;
            })
            ->editColumn('id', function ($employee) {
                // Usamos el índice generado por DataTables (o el id real si prefieres).
                return $employee->id;
            })
            ->blacklist(['image', 'action'])
            ->rawColumns(['name', 'action'])
            ->escapeColumns([])
            ->make(true);
    }

    public function getVisitor($id)
    {
        // Verificar que el empleado pertenezca a la sede del supervisor
        if (auth()->user()->hasRole('supervisor')) {
            $employee = Employee::find($id);
            if (!$employee || $employee->headquarters_id != $this->getSupervisorHeadquartersId()) {
                return Datatables::of(collect())->make(true);
            }
        }
        
        $visitors = VisitingDetails::where(['employee_id' => $id])->orderBy('id', 'desc')->get();

        $i            = 1;
        $visitorArray = [];
        if (!blank($visitors)) {
            foreach ($visitors as $visitor) {
                $visitorArray[$i]          = $visitor;
                $visitorArray[$i]['setID'] = $i;
                $i++;
            }
        }
        return Datatables::of($visitorArray)
            ->addColumn('action', function ($visitor) {
                $retAction = '';

                if (auth()->user()->can('pre-registers_show')) {
                    $retAction .= '<a href="' . route('admin.visitors.show', $visitor) . '" class="btn btn-sm btn-icon mr-2  float-left btn-info" data-toggle="tooltip" data-placement="top" title="View"><i class="far fa-eye"></i></a>';
                }

                if (auth()->user()->can('pre-registers_edit')) {
                    $retAction .= '<a href="' . route('admin.visitors.edit', $visitor) . '" class="btn btn-sm btn-icon float-left btn-primary" data-toggle="tooltip" data-placement="top" title="Edit"> <i class="far fa-edit"></i></a>';
                }


                if (auth()->user()->can('pre-registers_delete')) {
                    $retAction .= '<form class="float-left pl-2" action="' . route('admin.visitors.destroy', $visitor) . '" method="POST">' . method_field('DELETE') . csrf_field() . '<button class="btn btn-sm btn-icon btn-danger" data-toggle="tooltip" data-placement="top" title="Delete"> <i class="fa fa-trash"></i></button></form>';
                }

                return $retAction;
            })

            ->editColumn('name', function ($visitor) {
                return Str::limit(optional($visitor->visitor)->name, 50);
            })
            ->addColumn('image', function ($visitor) {
                return '<figure class="avatar mr-2"><img src="' . $visitor->images . '" alt=""></figure>';
            })
            ->editColumn('email', function ($visitor) {
                return Str::limit(optional($visitor->visitor)->email, 50);
            })

            ->editColumn('date', function ($visitor) {
                return date('d-m-Y h:i A', strtotime($visitor->checkin_at));
            })

            ->editColumn('id', function ($visitor) {
                return $visitor->setID;
            })
            ->rawColumns(['name', 'action'])
            ->escapeColumns([])
            ->make(true);
    }

    public function getPreRegister($id)
    {

        $pre_registers = PreRegister::where(['employee_id' => $id])->orderBy('id', 'desc')->get();

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
                $retAction = '';

                if (auth()->user()->can('pre-registers_show')) {
                    $retAction .= '<a href="' . route('admin.pre-registers.show', $pre_register) . '" class="btn btn-sm btn-icon mr-2  float-left btn-info" data-toggle="tooltip" data-placement="top" title="View"><i class="far fa-eye"></i></a>';
                }

                if (auth()->user()->can('pre-registers_edit')) {
                    $retAction .= '<a href="' . route('admin.pre-registers.edit', $pre_register) . '" class="btn btn-sm btn-icon float-left btn-primary" data-toggle="tooltip" data-placement="top" title="Edit"> <i class="far fa-edit"></i></a>';
                }


                if (auth()->user()->can('pre-registers_delete')) {
                    $retAction .= '<form class="float-left pl-2" action="' . route('admin.pre-registers.destroy', $pre_register) . '" method="POST">' . method_field('DELETE') . csrf_field() . '<button class="btn btn-sm btn-icon btn-danger" data-toggle="tooltip" data-placement="top" title="Delete"> <i class="fa fa-trash"></i></button></form>';
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

            ->editColumn('expected_date', function ($pre_register) {
                if (optional($pre_register->visitor)->is_pre_register == 1) {
                    $date = '<p class="text-danger">' . $pre_register->expected_date . '</p>';
                } else {
                    $date = '<p>' . $pre_register->expected_date . '</p>';
                }
                return $date;
            })
            ->editColumn('expected_time', function ($pre_register) {
                if (optional($pre_register->visitor)->is_pre_register == 1) {
                    $time = '<p class="text-danger">' . date('h:i A', strtotime($pre_register->expected_time)) . '</p>';
                } else {
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
