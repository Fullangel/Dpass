<?php

namespace App\Http\Controllers\Admin;

use DB;
use App\Enums\Status;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use DateTimeImmutable;
use App\Models\Visitor;
use App\Models\Employee;
use App\Models\Headquarters;
use App\Models\Invitation;
use App\Models\PreRegister;
use App\Models\Region;
use Illuminate\Support\Env;
use Illuminate\Support\Str;
use App\Enums\VisitorStatus;
use Illuminate\Http\Request;
use App\Models\VisitingDetails;
use Yajra\DataTables\DataTables;
use App\Http\Services\SmsService;
use App\Http\Controllers\Controller;
use App\Http\Requests\VisitorRequest;
use Illuminate\Support\Facades\Validator;
use App\Notifications\VisitorConfirmation;
use App\Http\Controllers\BackendController;
use App\Http\Services\Visitor\VisitorService;
use Spatie\ImageOptimizer\OptimizerChainFactory;

class VisitorController extends BackendController
{
    protected $visitorService;

    public function __construct(VisitorService $visitorService)
    {
        parent::__construct();
        $this->visitorService = $visitorService;
        $this->middleware('auth');
        $this->data['sitetitle'] = 'Visitors';

        $this->middleware(['permission:visitors'])->only('index');
        $this->middleware(['permission:visitors_create'])->only('create', 'store');
        $this->middleware(['permission:visitors_edit'])->only('edit', 'update');
        $this->middleware(['permission:visitors_delete'])->only('destroy');
        $this->middleware(['permission:visitors_show'])->only('show');
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        return view('admin.visitor.index');
    }


    public function create(Request $request)
    {
        $this->data['employees'] = $this->employeesForVisitorForm();
        $this->data['regions'] = Region::all();
        $this->data['headquarters'] = Headquarters::all();

        return view('admin.visitor.create', $this->data);
    }

    /**
     * Obtener región y sede del funcionario seleccionado
     */
    public function getEmployeeRegionHeadquarters(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id'
        ]);

        $employee = Employee::with(['region', 'headquarters'])->find($request->employee_id);

        if (!$employee || !$this->employeeAllowedForCurrentUser((int) $employee->id)) {
            return response()->json(['message' => 'Funcionario no disponible para su sede.'], 403);
        }

        return response()->json([
            'region_id' => $employee->region_id,
            'region_name' => $employee->region->name ?? null,
            'headquarters_id' => $employee->headquarters_id,
            'headquarters_name' => $employee->headquarters->name ?? null
        ]);
    }

    public function store(VisitorRequest $request)
    {
        if (!$this->employeeAllowedForCurrentUser((int) $request->input('employee_id'))) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['employee_id' => 'El funcionario seleccionado no pertenece a su sede.']);
        }

        $visitingDetail = $this->visitorService->make($request);
        
        // Optimizar imagen si existe
        if ($visitingDetail->getFirstMediaUrl('visitor')) {
            try {
                $media = $visitingDetail->getFirstMedia('visitor');
                if ($media && file_exists($media->getPath())) {
                    $optimizerChain = OptimizerChainFactory::create();
                    $optimizerChain->optimize($media->getPath());
                }
            } catch (\Exception $e) {
                // Silenciar errores de optimización
            }
        }

        if (setting('whatsapp_message')) {
            return redirect()->route('admin.visitors.show',$visitingDetail->id);

        }

        return redirect()->route('admin.visitors.index')->withSuccess('The data inserted successfully!');
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
        $this->data['visitingDetails'] = $this->visitorService->find($id);
        if ($this->data['visitingDetails']) {
            if (!$this->visitBelongsToUserHeadquarters($this->data['visitingDetails'])) {
                return redirect()->route('admin.visitors.index')->withError('No tiene permiso para ver esta visita.');
            }
            return view('admin.visitor.show', $this->data);
        } else {
            return redirect()->route('admin.visitors.index');
        }
    }
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'visitorID' => 'required|numeric',
        ], [
            'visitorID.required' => 'Visitor ID required',
            'visitorID.numeric' => 'ID must be numeric'
        ]);

        if ($validator->fails()) {
            return redirect(route('admin.visitors.index'))->withError($validator->errors()->first('visitorID'));
        };

        $id = $request->visitorID;

        $visitingDetail = VisitingDetails::where('reg_no', $id)->first();
        if ($visitingDetail && (!$visitingDetail->checkout_at)) {
            $visitingDetail->checkout_at = date('Y-m-d H:i');
            $visitingDetail->save();
            return redirect()->route('admin.visitors.index')->withSuccess('Successfully Checked-Out!');
        } elseif (!$visitingDetail) {
            return redirect()->route('admin.visitors.index')->withError('ID not found');
        } else {

            return redirect()->route('admin.visitors.index')->withError('Already Checked-Out!');
        }
    }

    public function edit($id)
    {
        $this->data['visitingDetails'] = $this->visitorService->find($id);
        if (!$this->data['visitingDetails']) {
            return redirect()->route('admin.visitors.index');
        }
        
        if (!$this->visitBelongsToUserHeadquarters($this->data['visitingDetails'])) {
            return redirect()->route('admin.visitors.index')->withError('No tiene permiso para editar esta visita.');
        }

        $this->data['employees'] = $this->employeesForVisitorForm();
        $this->data['regions'] = Region::all();
        $this->data['headquarters'] = Headquarters::all();
        
        return view('admin.visitor.edit', $this->data);
    }

    public function update(VisitorRequest $request, VisitingDetails $visitor)
    {
        if (!$this->visitBelongsToUserHeadquarters($visitor)) {
            return redirect()->route('admin.visitors.index')->withError('No tiene permiso para actualizar esta visita.');
        }

        if (!$this->employeeAllowedForCurrentUser((int) $request->input('employee_id'))) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['employee_id' => 'El funcionario seleccionado no pertenece a su sede.']);
        }

        $visitingDetail = $this->visitorService->update($request, $visitor->id);
        // Optimizar imagen si existe
        if ($visitingDetail->getFirstMediaUrl('visitor')) {
            try {
                $media = $visitingDetail->getFirstMedia('visitor');
                if ($media && file_exists($media->getPath())) {
                    $optimizerChain = OptimizerChainFactory::create();
                    $optimizerChain->optimize($media->getPath());
                }
            } catch (\Exception $e) {
                // Silenciar errores de optimización
            }
        }
        return redirect()->route('admin.visitors.index')->withSuccess('The data updated successfully!');
    }

    public function destroy($id)
    {
        $visitor = VisitingDetails::find($id);
        if (!$visitor || !$this->visitBelongsToUserHeadquarters($visitor)) {
            return redirect()->route('admin.visitors.index')->withError('No tiene permiso para eliminar esta visita.');
        }

        $this->visitorService->delete($id);
        return redirect()->route('admin.visitors.index')->withSuccess('The data delete successfully!');
    }


    public function getVisitor(Request $request)
    {
        // Construimos un query Eloquent optimizado y lo dejamos en manos de DataTables (server-side real).
        // Esto evita cargar todos los registros en memoria y elimina los timeouts.
        $user = auth()->user();

        $query = VisitingDetails::query()
            ->with(['visitor', 'employee.user', 'region', 'headquarters'])
            ->orderByDesc('id');

        // Filtro por rol / sede equivalente a VisitorService::all()
        if ($user && $user->getrole) {
            $roleName = $user->getrole->name;

            if ($roleName === 'Employee') {
                if ($user->employee) {
                    $query->where('employee_id', $user->employee->id);
                } else {
                    // Sin empleado asociado: no debe ver visitas
                    $query->whereRaw('1 = 0');
                }
            } elseif ($roleName === 'supervisor' || $roleName === 'Reception') {
                if ($user->employee && $user->employee->headquarters_id) {
                    $query->where('headquarters_id', $user->employee->headquarters_id);
                } else {
                    $query->whereRaw('1 = 0');
                }
            }
            // Admin y otros roles ven todas las visitas (sin filtro extra)
        }

        // Filtros adicionales por estado (si DataTables los envía)
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $likeOperator = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        // Usamos DataTables sobre el query Eloquent (no método estático eloquent en esta versión).
        // Búsqueda global: columnas presentadas (name, location, date, checkout, image, action)
        // no existen en visiting_details; filterColumn evita SQL inválido (p. ej. visiting_details.name).
        return Datatables::of($query)
            ->addIndexColumn()

            ->filterColumn('name', function ($query, $keyword) use ($likeOperator) {
                $term = '%' . addcslashes((string) $keyword, '%_\\') . '%';
                $query->whereHas('visitor', function ($q) use ($term, $likeOperator) {
                    $q->where(function ($inner) use ($term, $likeOperator) {
                        $inner->where('first_name', $likeOperator, $term)
                            ->orWhere('last_name', $likeOperator, $term)
                            ->orWhere('national_identification_no', $likeOperator, $term)
                            ->orWhere('phone', $likeOperator, $term)
                            ->orWhere('email', $likeOperator, $term);
                    });
                });
            })
            ->filterColumn('visitor_id', function ($query, $keyword) use ($likeOperator) {
                $term = '%' . addcslashes((string) $keyword, '%_\\') . '%';
                $query->whereHas('visitor', function ($q) use ($term, $likeOperator) {
                    $q->where('national_identification_no', $likeOperator, $term);
                });
            })
            ->filterColumn('employee_id', function ($query, $keyword) use ($likeOperator) {
                $term = '%' . addcslashes((string) $keyword, '%_\\') . '%';
                $query->where(function ($outer) use ($term, $likeOperator) {
                    $outer->whereHas('employee.user', function ($q) use ($term, $likeOperator) {
                        $q->where(function ($inner) use ($term, $likeOperator) {
                            $inner->where('first_name', $likeOperator, $term)
                                ->orWhere('last_name', $likeOperator, $term)
                                ->orWhere('email', $likeOperator, $term);
                        });
                    })->orWhereHas('employee', function ($q) use ($term, $likeOperator) {
                        $q->where(function ($inner) use ($term, $likeOperator) {
                            $inner->where('first_name', $likeOperator, $term)
                                ->orWhere('last_name', $likeOperator, $term);
                        });
                    });
                });
            })
            ->filterColumn('location', function ($query, $keyword) use ($likeOperator) {
                $term = '%' . addcslashes((string) $keyword, '%_\\') . '%';
                $query->where(function ($q) use ($term, $likeOperator) {
                    $q->whereHas('region', function ($rq) use ($term, $likeOperator) {
                        $rq->where('name', $likeOperator, $term);
                    })->orWhereHas('headquarters', function ($hq) use ($term, $likeOperator) {
                        $hq->where('name', $likeOperator, $term);
                    });
                });
            })
            ->filterColumn('date', function ($query, $keyword) use ($likeOperator) {
                $term = '%' . addcslashes((string) $keyword, '%_\\') . '%';
                $query->where('checkin_at', $likeOperator, $term);
            })
            ->filterColumn('checkout', function ($query, $keyword) use ($likeOperator) {
                $term = '%' . addcslashes((string) $keyword, '%_\\') . '%';
                $query->where('checkout_at', $likeOperator, $term);
            })

            ->addColumn('action', function ($visitingDetail) {
                $retAction = '';

                if ((auth()->user()->can('visitors_show')) && (!$visitingDetail->checkout_at) && $visitingDetail->status == VisitorStatus::ACCEPT) {
                    $retAction .= '<a href="' . route('admin.visitors.checkout', $visitingDetail) . '" class="btn btn-sm btn-icon mr-1 float-left btn-success" data-toggle="tooltip" data-placement="top" title="Check-Out"><i class="fas fa-sign-out-alt"></i></a>';
                }

                if (auth()->user()->can('visitors_show')) {
                    $retAction .= '<a href="' . route('admin.visitors.disable', $visitingDetail->id) . '" class="btn btn-sm btn-icon mr-1 float-left btn-'.(!$visitingDetail->disable ? "danger" : "success").'" data-toggle="tooltip" data-placement="top" title="'.(!$visitingDetail->disable ? "Block Visitor" : "Unblock Visitor").'"><i class="fa fa-ban"></i></a>';
                }

                if (auth()->user()->can('visitors_show')) {
                    $retAction .= '<a href="' . route('admin.visitors.show', $visitingDetail) . '" class="btn btn-sm btn-icon mr-1 float-left btn-info" data-toggle="tooltip" data-placement="top" title="View"><i class="far fa-eye"></i></a>';
                }

                if (auth()->user()->can('visitors_edit')) {
                    $retAction .= '<a href="' . route('admin.visitors.edit', $visitingDetail) . '" class="btn btn-sm btn-icon mr-1 float-left btn-primary" data-toggle="tooltip" data-placement="top" title="Edit"> <i class="far fa-edit"></i></a>';
                }

                if (auth()->user()->can('visitors_delete')) {
                    $retAction .= '<form class="float-left  " action="' . route('admin.visitors.destroy', $visitingDetail) . '" method="POST">' . method_field('DELETE') . csrf_field() . '<button class="btn btn-sm btn-icon btn-danger" onclick="return confirmDelete()" data-toggle="tooltip" data-placement="top" title="Delete"> <i class="fa fa-trash"></i></button></form>';
                }

                return $retAction;
            })

            ->editColumn('name', function ($visitingDetail) {
                return Str::limit(optional($visitingDetail->visitor)->name, 50);
            })
            ->addColumn('image', function ($visitingDetail) {
                return '<figure class="avatar mr-2"><img src="' . $visitingDetail->images . '" alt=""></figure>';
            })
            ->editColumn('visitor_id', function ($visitingDetail) {
                return optional($visitingDetail->visitor)->national_identification_no;
            })

            ->editColumn('phone', function ($visitingDetail) {
                return Str::limit(optional($visitingDetail->visitor)->phone, 50);
            })
            ->editColumn('employee_id', function ($visitingDetail) {
                return optional(optional($visitingDetail->employee)->user)->name;
            })
            ->editColumn('location', function ($visitingDetail) {
                $region = optional($visitingDetail->region)->name;
                $headquarters = optional($visitingDetail->headquarters)->name;
                return $region && $headquarters ? $region . ' - ' . $headquarters : ($region ?? $headquarters ?? 'N/A');
            })
            ->editColumn('status', function ($visitingDetail) {
                $drop = '';
                $dropActive = false;
                $activeStatus = 'Change Status';
                foreach (trans("visitor_statuses") as $key => $status) {
                    if ($visitingDetail->status == $key) {
                        $activeStatus = $status;
                    }

                    if ($visitingDetail->status != VisitorStatus::ACCEPT && $key != $visitingDetail->status) {
                        if ($visitingDetail->status == VisitorStatus::REJECT) {
                            $dropActive = false;
                        } else {
                            $drop .= '<a class="dropdown-item" href="' . route('admin.visitor.change-status', [$visitingDetail->id, $key,0]) . '" >' . $status . '</a>';
                            $dropActive = true;
                        }
                    }
                }
                if ($dropActive) {
                    return '<div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'
                        . $activeStatus
                        . '</button>
                  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">' . $drop . '</div></div>';
                } else {
                    return '<span class="badge ' . ($visitingDetail->status == VisitorStatus::ACCEPT ? 'badge-success' : 'badge-danger') . '">' . $activeStatus . '</span>';
                }
            })

            ->editColumn('date', function ($visitingDetail) {
                if ($visitingDetail->checkin_at) {
                    return date('d-M-Y h:i A', strtotime($visitingDetail->checkin_at));
                } else {
                    return 'N/A';
                }
            })
            ->editColumn('checkout', function ($visitingDetail) {
                if ($visitingDetail->checkout_at) {
                    return date('d-M-Y h:i A', strtotime($visitingDetail->checkout_at));
                } else {
                    return 'N/A';
                }
            })

            ->editColumn('id', function ($visitingDetail) {
                return $visitingDetail->setID;
            })
            ->blacklist(['image', 'action'])
            ->rawColumns(['action', 'status', 'image'])
            ->escapeColumns([])
            ->make(true);
    }
    public function checkout(VisitingDetails $visitingDetail)
    {

        $visitingDetail->checkout_at = date('Y-m-d H:i');
        $visitingDetail->save();
        return redirect()->route('admin.visitors.index')->withSuccess('Successfully Check-Out!');
    }

    public function changeStatus($id, $status,$dashboard=false)
    {
        $visitor         = VisitingDetails::findOrFail($id);
        $visitor->status = $status;
        $visitor->checkin_at = date('Y-m-d H:i');
        $visitor->save();

        try {
            $visitor->visitor->notify(new VisitorConfirmation($visitor));

        } catch (\Exception $e) {

        }

        if($dashboard){
            return redirect()->route('admin.dashboard.index')->withSuccess('The Status Change successfully!');
        }
        return redirect()->route('admin.visitors.index');
    }

    public function visitorDisable($id)
    {
        $visitor         = VisitingDetails::findOrFail($id);
        if (!$visitor->disable) {
            $visitor->disable = true;
        }else{
            $visitor->disable = false;
        }
        $visitor->save();

        return redirect()->back()->withSuccess('Visitor Disable successfully!');
    }

    /**
     * Sede del usuario recepción/supervisor (vía registro employee), o null si no aplica.
     */
    private function getUserHeadquartersId(): ?int
    {
        $user = auth()->user();
        if (!$user || (!$user->hasRole('supervisor') && !$user->hasRole('Reception'))) {
            return null;
        }

        $headquartersId = optional($user->employee)->headquarters_id;

        return $headquartersId ? (int) $headquartersId : null;
    }

    /**
     * Funcionarios visibles al registrar visitante: recepción/supervisor solo los de su sede.
     */
    private function employeesForVisitorForm()
    {
        $query = Employee::query()->where('status', Status::ACTIVE);

        $headquartersId = $this->getUserHeadquartersId();
        if ($headquartersId) {
            $query->where('headquarters_id', $headquartersId);
        }

        return $query->orderBy('first_name')->orderBy('last_name')->get();
    }

    private function employeeAllowedForCurrentUser(?int $employeeId): bool
    {
        if (!$employeeId) {
            return false;
        }

        $headquartersId = $this->getUserHeadquartersId();
        if (!$headquartersId) {
            return Employee::where('id', $employeeId)->where('status', Status::ACTIVE)->exists();
        }

        return Employee::where('id', $employeeId)
            ->where('status', Status::ACTIVE)
            ->where('headquarters_id', $headquartersId)
            ->exists();
    }

    private function visitBelongsToUserHeadquarters(VisitingDetails $visit): bool
    {
        $headquartersId = $this->getUserHeadquartersId();
        if (!$headquartersId) {
            return true;
        }

        return (int) $visit->headquarters_id === $headquartersId;
    }
}
