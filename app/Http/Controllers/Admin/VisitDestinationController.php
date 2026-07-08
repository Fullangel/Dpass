<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Status;
use App\Http\Controllers\BackendController;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Headquarters;
use App\Models\User;
use App\Models\VisitDestination;
use App\Models\VisitDestinationRule;
use App\Services\VisitDestinationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;

class VisitDestinationController extends BackendController
{
    public function __construct(private VisitDestinationService $visitDestinationService)
    {
        parent::__construct();
        $this->middleware('auth');
        $this->data['sitetitle'] = 'Destinos de visita';

        $this->middleware(['visit_destination.access:view'])->only('index', 'getVisitDestinations');
        $this->middleware(['visit_destination.access:create'])->only('create', 'store');
        $this->middleware(['visit_destination.access:edit'])->only('edit', 'update', 'storeRule', 'destroyRule');
        $this->middleware(['visit_destination.access:delete'])->only('destroy');
    }

    public function index()
    {
        $user = auth()->user();
        $this->data['canCreate'] = $this->visitDestinationService->userCanCreate($user);
        $this->data['canEdit'] = $this->visitDestinationService->userCanEdit($user);
        $this->data['canDelete'] = $this->visitDestinationService->userCanDelete($user);

        return view('admin.visit-destination.index', $this->data);
    }

    public function create()
    {
        $this->data['headquarters'] = $this->headquartersForForm();
        $this->data['assignableUsers'] = $this->assignableUsersForHeadquarters(
            old('headquarters_id') ? (int) old('headquarters_id') : null
        );

        return view('admin.visit-destination.create', $this->data);
    }

    public function store(Request $request)
    {
        $validated = $this->validatedDestinationPayload($request);
        $destination = VisitDestination::query()->create($validated);

        $userIds = collect($request->input('user_ids', []))
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $destination->users()->sync($this->filterAssignableUserIds($userIds, $validated['headquarters_id'] ?? null));
        VisitDestinationService::clearRulesCache();

        return redirect()
            ->route('admin.visit-destinations.edit', $destination)
            ->withSuccess('Destino creado correctamente. Configure las reglas de deteccion.');
    }

    public function edit(VisitDestination $visitDestination)
    {
        $this->authorizeDestination($visitDestination);

        $visitDestination->load(['rules', 'users']);

        $this->data['visitDestination'] = $visitDestination;
        $this->data['headquarters'] = $this->headquartersForForm();
        $this->data['employees'] = $this->employeesForDestination($visitDestination);
        $this->data['departments'] = Department::query()->where('status', Status::ACTIVE)->orderBy('name')->get();
        $this->data['designations'] = Designation::query()->where('status', Status::ACTIVE)->orderBy('name')->get();
        $this->data['assignableUsers'] = $this->assignableUsersForDestination($visitDestination);
        $this->data['assignedUserIds'] = $visitDestination->users->pluck('id')->all();

        return view('admin.visit-destination.edit', $this->data);
    }

    public function update(Request $request, VisitDestination $visitDestination)
    {
        $this->authorizeDestination($visitDestination);

        $validated = $this->validatedDestinationPayload($request, $visitDestination->id);
        $visitDestination->update($validated);

        $userIds = collect($request->input('user_ids', []))
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $visitDestination->users()->sync($this->filterAssignableUserIds($userIds, $validated['headquarters_id'] ?? null));
        VisitDestinationService::clearRulesCache();

        return redirect()
            ->route('admin.visit-destinations.edit', $visitDestination)
            ->withSuccess('Destino actualizado correctamente.');
    }

    public function destroy(VisitDestination $visitDestination)
    {
        $this->authorizeDestination($visitDestination);

        if (! $this->visitDestinationService->destinationCanBeDeleted($visitDestination)) {
            return redirect()
                ->route('admin.visit-destinations.index')
                ->withError('No se puede eliminar este destino porque ya tiene visitas registradas. Puede desactivarlo cambiando su estado.');
        }

        $visitDestination->delete();
        VisitDestinationService::clearRulesCache();

        return redirect()
            ->route('admin.visit-destinations.index')
            ->withSuccess('Destino eliminado correctamente.');
    }

    public function storeRule(Request $request, VisitDestination $visitDestination)
    {
        $this->authorizeDestination($visitDestination);

        $validated = $request->validate([
            'rule_type' => ['required', Rule::in([
                VisitDestinationRule::TYPE_EMPLOYEE,
                VisitDestinationRule::TYPE_DEPARTMENT,
                VisitDestinationRule::TYPE_DESIGNATION,
            ])],
            'rule_value' => ['required', 'integer', 'min:1'],
        ]);

        VisitDestinationRule::query()->updateOrCreate(
            [
                'visit_destination_id' => $visitDestination->id,
                'rule_type' => $validated['rule_type'],
                'rule_value' => (string) $validated['rule_value'],
            ],
            ['status' => Status::ACTIVE]
        );

        VisitDestinationService::clearRulesCache();

        return redirect()
            ->route('admin.visit-destinations.edit', $visitDestination)
            ->withSuccess('Regla agregada correctamente.');
    }

    public function destroyRule(VisitDestination $visitDestination, VisitDestinationRule $rule)
    {
        $this->authorizeDestination($visitDestination);

        if ((int) $rule->visit_destination_id !== (int) $visitDestination->id) {
            abort(404);
        }

        $rule->delete();
        VisitDestinationService::clearRulesCache();

        return redirect()
            ->route('admin.visit-destinations.edit', $visitDestination)
            ->withSuccess('Regla eliminada correctamente.');
    }

    public function getVisitDestinations()
    {
        $query = VisitDestination::query()
            ->with('headquarters')
            ->withCount(['users', 'rules'])
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($headquartersId = $this->currentUserHeadquartersId()) {
            $query->where(function ($builder) use ($headquartersId) {
                $builder->whereNull('headquarters_id')
                    ->orWhere('headquarters_id', $headquartersId);
            });
        }

        return Datatables::of($query)
            ->addColumn('headquarters_name', fn ($destination) => optional($destination->headquarters)->name ?? 'Todas')
            ->editColumn('users_count', fn ($destination) => (int) $destination->users_count)
            ->editColumn('rules_count', fn ($destination) => (int) $destination->rules_count)
            ->editColumn('status', fn ($destination) => trans('statuses.' . $destination->status))
            ->addColumn('action', function ($destination) {
                $user = auth()->user();
                $retAction = '';

                if ($this->visitDestinationService->userCanEdit($user)) {
                    $retAction .= '<a href="' . route('admin.visit-destinations.edit', $destination) . '" class="btn btn-sm btn-icon float-left btn-primary mr-1" data-toggle="tooltip" title="Editar"><i class="far fa-edit"></i></a>';
                }

                if ($this->visitDestinationService->userCanDelete($user)
                    && $this->visitDestinationService->destinationCanBeDeleted($destination)) {
                    $retAction .= '<form class="float-left" action="' . route('admin.visit-destinations.destroy', $destination) . '" method="POST">' . method_field('DELETE') . csrf_field() . '<button type="submit" class="btn btn-sm btn-icon btn-danger" onclick="return confirm(\'¿Eliminar este destino?\')" data-toggle="tooltip" title="Eliminar"><i class="fa fa-trash"></i></button></form>';
                }

                return $retAction;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    private function validatedDestinationPayload(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('visit_destinations', 'slug')->ignore($ignoreId),
            ],
            'headquarters_id' => ['nullable', 'integer', 'exists:headquarters,id'],
            'status' => ['required', 'integer'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ], [], [
            'name' => 'nombre',
            'slug' => 'identificador',
            'headquarters_id' => 'sede',
        ]);

        $validated['slug'] = Str::slug($validated['slug'] ?: $validated['name']);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['headquarters_id'] = $validated['headquarters_id'] ?: null;

        return $validated;
    }

    private function authorizeDestination(VisitDestination $visitDestination): void
    {
        $headquartersId = $this->currentUserHeadquartersId();
        if (! $headquartersId) {
            return;
        }

        if ($visitDestination->headquarters_id && (int) $visitDestination->headquarters_id !== $headquartersId) {
            abort(403, 'No tiene permiso para gestionar este destino.');
        }
    }

    private function currentUserHeadquartersId(): ?int
    {
        $user = auth()->user();
        if (! $user || ! $user->hasRole('supervisor')) {
            return null;
        }

        return optional($user->employee)->headquarters_id ? (int) $user->employee->headquarters_id : null;
    }

    private function headquartersForForm()
    {
        $headquartersId = $this->currentUserHeadquartersId();
        if ($headquartersId) {
            return Headquarters::query()->where('id', $headquartersId)->get();
        }

        return Headquarters::query()->orderBy('name')->get();
    }

    private function employeesForDestination(VisitDestination $visitDestination)
    {
        $query = Employee::query()->where('status', Status::ACTIVE)->orderBy('first_name')->orderBy('last_name');

        if ($visitDestination->headquarters_id) {
            $query->where('headquarters_id', $visitDestination->headquarters_id);
        } elseif ($headquartersId = $this->currentUserHeadquartersId()) {
            $query->where('headquarters_id', $headquartersId);
        }

        return $query->get();
    }

    private function assignableUsersForDestination(VisitDestination $visitDestination)
    {
        return $this->assignableUsersForHeadquarters($visitDestination->headquarters_id);
    }

    private function assignableUsersForHeadquarters(?int $headquartersId = null)
    {
        $query = User::query()
            ->where('status', Status::ACTIVE)
            ->whereHas('roles', function ($roleQuery) {
                $roleQuery->where('name', 'Reception');
            })
            ->orderBy('first_name')
            ->orderBy('last_name');

        if ($headquartersId) {
            $query->whereHas('employee', function ($employeeQuery) use ($headquartersId) {
                $employeeQuery->where('headquarters_id', $headquartersId);
            });
        } elseif ($scopedHeadquartersId = $this->currentUserHeadquartersId()) {
            $query->whereHas('employee', function ($employeeQuery) use ($scopedHeadquartersId) {
                $employeeQuery->where('headquarters_id', $scopedHeadquartersId);
            });
        }

        return $query->get();
    }

    private function filterAssignableUserIds(array $userIds, ?int $headquartersId): array
    {
        if ($userIds === []) {
            return [];
        }

        $allowedIds = $this->assignableUsersForHeadquarters($headquartersId)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return collect($userIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => in_array($id, $allowedIds, true))
            ->unique()
            ->values()
            ->all();
    }
}
