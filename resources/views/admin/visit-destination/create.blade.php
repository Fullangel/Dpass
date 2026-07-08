@extends('admin.layouts.master')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/modules/select2/dist/css/select2.min.css') }}">
@endsection

@section('main-content')
<section class="section">
    <div class="section-header">
        <h1>Destinos de visita</h1>
        {{ Breadcrumbs::render('visit-destinations/add') }}
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card">
                    <form action="{{ route('admin.visit-destinations.store') }}" method="POST">
                        @csrf
                        <div class="card-body">
                            <div class="form-group">
                                <label>Nombre</label> <span class="text-danger">*</span>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group">
                                <label>Identificador (slug)</label>
                                <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug') }}" placeholder="Se genera automaticamente si se deja vacio">
                                @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group">
                                <label>Sede</label>
                                <select name="headquarters_id" id="headquarters_id" class="form-control @error('headquarters_id') is-invalid @enderror">
                                    <option value="">Todas / sin sede fija</option>
                                    @foreach($headquarters as $headquarter)
                                        <option value="{{ $headquarter->id }}" {{ (string) old('headquarters_id') === (string) $headquarter->id ? 'selected' : '' }}>
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
                                            <option value="{{ $key }}" {{ (string) old('status', \App\Enums\Status::ACTIVE) === (string) $key ? 'selected' : '' }}>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Orden</label>
                                    <input type="number" min="0" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Recepcionistas asignadas (rol Reception)</label>
                                <select name="user_ids[]" id="user_ids" class="form-control select2" multiple>
                                    @foreach($assignableUsers as $assignableUser)
                                        <option value="{{ $assignableUser->id }}"
                                            {{ in_array($assignableUser->id, old('user_ids', []), true) ? 'selected' : '' }}>
                                            {{ $assignableUser->name }} ({{ $assignableUser->email }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">
                                    Puede asignar una o mas recepcionistas. Solo usuarios con rol Reception de la sede seleccionada.
                                    Despues de crear podra definir las reglas de deteccion.
                                </small>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button class="btn btn-primary" type="submit">{{ __('levels.submit') }}</button>
                            <a href="{{ route('admin.visit-destinations.index') }}" class="btn btn-light ml-2">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script src="{{ asset('assets/modules/select2/dist/js/select2.full.min.js') }}"></script>
<script src="{{ asset('js/visit-destination/create.js') }}"></script>
@endsection
