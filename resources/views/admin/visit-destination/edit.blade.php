@extends('admin.layouts.master')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/modules/select2/dist/css/select2.min.css') }}">
@endsection

@section('main-content')
<section class="section">
    <div class="section-header">
        <h1>Destinos de visita</h1>
        {{ Breadcrumbs::render('visit-destinations/edit') }}
    </div>

    <div class="section-body">
        @if(!app(\App\Services\VisitDestinationService::class)->destinationCanBeDeleted($visitDestination))
            <div class="alert alert-warning">
                Este destino ya tiene visitas registradas. No puede eliminarse; si ya no aplica, cambie su estado a Inactivo.
            </div>
        @endif
        <div class="row">
            <div class="col-12 col-lg-7">
                <div class="card">
                    <div class="card-header"><h4>Datos del destino</h4></div>
                    <form action="{{ route('admin.visit-destinations.update', $visitDestination) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="form-group">
                                <label>Nombre</label> <span class="text-danger">*</span>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $visitDestination->name) }}">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group">
                                <label>Identificador (slug)</label>
                                <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $visitDestination->slug) }}">
                                @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group">
                                <label>Sede</label>
                                <select name="headquarters_id" class="form-control @error('headquarters_id') is-invalid @enderror">
                                    <option value="">Todas / sin sede fija</option>
                                    @foreach($headquarters as $headquarter)
                                        <option value="{{ $headquarter->id }}" {{ (string) old('headquarters_id', $visitDestination->headquarters_id) === (string) $headquarter->id ? 'selected' : '' }}>
                                            {{ $headquarter->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('headquarters_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>{{ __('levels.status') }}</label> <span class="text-danger">*</span>
                                    <select name="status" class="form-control @error('status') is-invalid @enderror">
                                        @foreach(trans('statuses') as $key => $status)
                                            <option value="{{ $key }}" {{ (string) old('status', $visitDestination->status) === (string) $key ? 'selected' : '' }}>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Orden</label>
                                    <input type="number" min="0" name="sort_order" class="form-control" value="{{ old('sort_order', $visitDestination->sort_order) }}">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Recepcionistas asignadas (rol Reception)</label>
                                <select name="user_ids[]" id="user_ids" class="form-control select2" multiple>
                                    @foreach($assignableUsers as $assignableUser)
                                        <option value="{{ $assignableUser->id }}"
                                            {{ in_array($assignableUser->id, old('user_ids', $assignedUserIds), true) ? 'selected' : '' }}>
                                            {{ $assignableUser->name }} ({{ $assignableUser->email }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">
                                    Asigne una o mas recepcionistas con rol Reception. Veran la cola "Cola por destino" y recibiran notificaciones cuando llegue un visitante a este destino.
                                </small>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-between">
                            <div>
                                <button class="btn btn-primary" type="submit">Guardar destino y asignaciones</button>
                                <a href="{{ route('admin.visit-destinations.index') }}" class="btn btn-light ml-2">Volver al listado</a>
                            </div>
                            @if(app(\App\Services\VisitDestinationService::class)->userCanDelete(auth()->user()) && app(\App\Services\VisitDestinationService::class)->destinationCanBeDeleted($visitDestination))
                                <form action="{{ route('admin.visit-destinations.destroy', $visitDestination) }}" method="POST" onsubmit="return confirm('¿Eliminar este destino?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Eliminar destino</button>
                                </form>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-12 col-lg-5">
                <div class="card">
                    <div class="card-header"><h4>Reglas de deteccion</h4></div>
                    <div class="card-body">
                        @if($visitDestination->rules->isEmpty())
                            <p class="text-muted mb-3">Sin reglas configuradas.</p>
                        @else
                            <div class="table-responsive mb-3">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Tipo</th>
                                            <th>Valor</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($visitDestination->rules as $rule)
                                            <tr>
                                                <td>{{ $rule->rule_type }}</td>
                                                <td>{{ $rule->rule_value }}</td>
                                                <td class="text-right">
                                                    <form action="{{ route('admin.visit-destinations.rules.destroy', [$visitDestination, $rule]) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar regla?')">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        <form action="{{ route('admin.visit-destinations.rules.store', $visitDestination) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label>Tipo de regla</label>
                                <select name="rule_type" id="rule_type" class="form-control" required>
                                    <option value="employee_id">Funcionario (employee_id)</option>
                                    <option value="department_id">Gerencia / departamento</option>
                                    <option value="designation_id">Designacion</option>
                                </select>
                            </div>
                            <div class="form-group rule-value-group" data-type="employee_id">
                                <label>Funcionario</label>
                                <select name="rule_value" class="form-control rule-value-select" data-type="employee_id" disabled>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group rule-value-group d-none" data-type="department_id">
                                <label>Departamento</label>
                                <select class="form-control rule-value-select" data-type="department_id" disabled>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group rule-value-group d-none" data-type="designation_id">
                                <label>Designacion</label>
                                <select class="form-control rule-value-select" data-type="designation_id" disabled>
                                    @foreach($designations as $designation)
                                        <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-outline-primary btn-block">Agregar regla</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script src="{{ asset('assets/modules/select2/dist/js/select2.full.min.js') }}"></script>
<script src="{{ asset('js/visit-destination/edit.js') }}"></script>
@endsection
