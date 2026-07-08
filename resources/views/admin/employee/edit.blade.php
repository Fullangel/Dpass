@extends('admin.layouts.master')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/modules/select2/dist/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/modules/bootstrap-social/bootstrap-social.css') }}">
<link rel="stylesheet" href="{{ asset('assets/modules/summernote/summernote-bs4.css') }}">
<link rel="stylesheet" href="{{ asset('assets/modules/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}">
@endsection

@section('main-content')

<section class="section">
    <div class="section-header">
        <h1>{{ __('employee.employees') }}</h1>
        {{ Breadcrumbs::render('employees/edit') }}
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12 col-md-12 col-lg-12">
                <div class="card">
                    <form action="{{ route('admin.employees.update', $employee) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-group col">
                                    <label for="first_name">{{ __('employee.first_name') }}</label> <span
                                        class="text-danger">*</span>
                                    <input id="first_name" type="text" name="first_name"
                                        class="form-control {{ $errors->has('first_name') ? " is-invalid " : '' }}"
                                        value="{{ old('first_name',$employee->user->first_name) }}">
                                    @error('first_name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class="form-group col">
                                    <label for="last_name">{{ __('employee.last_name') }}</label> <span
                                        class="text-danger">*</span>
                                    <input id="last_name" type="text" name="last_name"
                                        class="form-control {{ $errors->has('last_name') ? " is-invalid " : '' }}"
                                        value="{{ old('last_name',$employee->user->last_name) }}">
                                    @error('last_name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                            </div>
                            <div class="form-row">
                                <div class="form-group col">
                                    <label>{{ __('employee.email_address') }}</label> <span class="text-danger">*</span>
                                    <input type="text" name="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        value="{{ old('email',$employee->user->email) }}">
                                    @error('email')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class="form-group col">
                                    <label>{{ __('employee.phone') }}</label> <span class="text-danger">*</span>
                                    <input type="text" name="phone"
                                        class="form-control @error('phone') is-invalid @enderror"
                                        value="{{ old('phone',$employee->user->phone) }}">
                                    @error('phone')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col">
                                    <label>{{ __('employee.joining_date') }}</label> <span class="text-danger">*</span>
                                    <input type="text" autocomplete="off" id="date-picker" name="date_of_joining"
                                        class="form-control @error('date_of_joining') is-invalid @enderror"
                                        value="{{ old('date_of_joining',$employee->date_of_joining) }}">
                                    @error('date_of_joining')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class="form-group col">
                                    <label for="gender">{{ __('employee.gender') }}</label> <span
                                        class="text-danger">*</span>
                                    <select id="gender" name="gender"
                                        class="form-control @error('gender') is-invalid @enderror">
                                        @foreach(trans('genders') as $key => $gender)
                                        <option value="{{ $key }}"
                                            {{ (old('gender',$employee->gender) == $key) ? 'selected' : '' }}>
                                            {{ $gender }}</option>
                                        @endforeach
                                    </select>
                                    @error('gender')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                            </div>
                            <div class="form-row">
                                <div class="form-group col">
                                    <label for="department_id">{{ __('employee.department') }}</label> <span
                                        class="text-danger">*</span>
                                    <select id="department_id" name="department_id"
                                        class="form-control @error('department_id') is-invalid @enderror">
                                        @foreach($departments as $key => $department)
                                        <option value="{{ $department->id }}"
                                            {{ (old('department_id',$employee->department_id) == $department->id) ? 'selected' : '' }}>
                                            {{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('department_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class="form-group col">
                                    <label for="designation_id">{{ __('employee.designation') }}</label> <span
                                        class="text-danger">*</span>
                                    <select id="designation_id" name="designation_id"
                                        class="form-control @error('designation_id') is-invalid @enderror">
                                        @foreach($designations as $key => $designation)
                                        <option value="{{ $designation->id }}"
                                            {{ (old('designation_id',$employee->designation_id) == $designation->id) ? 'selected' : '' }}>
                                            {{ $designation->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('designation_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class="form-group col">
                                    <label>{{ __('levels.status') }}</label> <span class="text-danger">*</span>
                                    <select name="status" class="form-control @error('status') is-invalid @enderror">
                                        @foreach(trans('statuses') as $key => $status)
                                        <option value="{{ $key }}"
                                            {{ (old('status', $employee->status) == $key) ? 'selected' : '' }}>
                                            {{ $status }}</option>
                                        @endforeach
                                    </select>
                                    @error('status')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                @if(!empty($can_assign_role))
                                <div class="form-group col">
                                    <label>{{ __('employee.role') }}</label> <span class="text-danger">*</span>
                                    <select name="role_id" class="form-control @error('role_id') is-invalid @enderror">
                                        <option value="">{{ __('employee.select_role') }}</option>
                                        @foreach($roles as $role)
                                        <option value="{{ $role->id }}"
                                            {{ (old('role_id', $employee_role_id) == $role->id) ? 'selected' : '' }}>
                                            {{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('role_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                @endif
                            </div>
                            @if(!empty($can_assign_role) && isset($visitDestinations))
                            <div class="form-row">
                                <div class="form-group col">
                                    <label>Destinos de visita (recepcion secundaria)</label>
                                    <select name="visit_destination_ids[]" id="visit_destination_ids" class="form-control select2" multiple>
                                        @foreach($visitDestinations as $visitDestination)
                                            <option value="{{ $visitDestination->id }}"
                                                {{ in_array($visitDestination->id, old('visit_destination_ids', $assignedVisitDestinationIds ?? []), true) ? 'selected' : '' }}>
                                                {{ $visitDestination->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">
                                        Asigne aqui la cola que vera este usuario cuando registren visitas hacia ese destino.
                                    </small>
                                </div>
                            </div>
                            @endif
                            <div class="form-row">
                                <div class="form-group col">
                                    <label for="region_id">{{ __('region.region') }} <span class="text-danger">*</span></label>
                                    <select id="region_id" name="region_id"
                                        class="form-control @error('region_id') is-invalid @enderror" required>
                                        <option value="">{{ __('region.select_region') }}</option>
                                        @foreach($regions as $region)
                                        <option value="{{ $region->id }}"
                                            {{ (old('region_id', $employee->region_id) == $region->id) ? 'selected' : '' }}>
                                            {{ $region->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('region_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class="form-group col">
                                    <label for="headquarters_id">{{ __('headquarters.headquarters') }} <span class="text-danger">*</span></label>
                                    <select id="headquarters_id" name="headquarters_id"
                                        class="form-control @error('headquarters_id') is-invalid @enderror" required>
                                        <option value="">{{ __('headquarters.select_headquarters') }}</option>
                                        @foreach($headquarters as $headquarter)
                                        <option value="{{ $headquarter->id }}" data-region="{{ $headquarter->region_id }}"
                                            {{ (old('headquarters_id', $employee->headquarters_id) == $headquarter->id) ? 'selected' : '' }}>
                                            {{ $headquarter->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('headquarters_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col">
                                    <label>{{ __('employee.current_password') }}</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" value="••••••••" readonly
                                            style="background-color: #e9ecef; cursor: not-allowed;">
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <i class="fas fa-lock"></i> {{ __('employee.password_set') }}
                                            </span>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">{{ __('employee.password_change_hint') }}</small>
                                </div>
                                <div class="form-group col">
                                    <label>{{ __('employee.new_password') }}</label>
                                    <input type="password" name="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        placeholder="{{ __('employee.leave_blank_to_keep') }}"
                                        autocomplete="new-password">
                                    @error('password')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class="form-group col">
                                    <label>{{ __('employee.confirm_new_password') }}</label>
                                    <input type="password" name="password_confirmation"
                                        class="form-control @error('password_confirmation') is-invalid @enderror"
                                        placeholder="{{ __('employee.confirm_new_password') }}"
                                        autocomplete="new-password">
                                    @error('password_confirmation')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col">
                                    <label for="about">{{ __('employee.about') }}</label>
                                    <textarea name="about" class="summernote-simple form-control height-textarea @error('about')
                                                      is-invalid @enderror" id="about">
                                    {{ old('about',$employee->about) }}
                                    </textarea>
                                    @error('about')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class="form-group col">
                                    <label for="customFile">{{ __('employee.image') }}</label>
                                    <div class="custom-file">
                                        <input name="image" type="file"
                                            class="custom-file-input @error('image') is-invalid @enderror"
                                            id="customFile" onchange="readURL(this);">
                                        <label class="custom-file-label"
                                            for="customFile">{{ __('employee.choose_file') }}</label>
                                    </div>
                                    @if ($errors->has('image'))
                                    <div class="help-block text-danger">
                                        {{ $errors->first('image') }}
                                    </div>
                                    @endif
                                    @if($employee->user->getFirstMediaUrl('user'))
                                    <img class="img-thumbnail image-width mt-4 mb-3" id="previewImage"
                                        src="{{ asset($employee->user->getFirstMediaUrl('user')) }}" alt="your image" />
                                    @else
                                    <img class="img-thumbnail image-width mt-4 mb-3" id="previewImage"
                                        src="{{ asset('assets/img/default/user.png') }}" alt="your image" />
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="card-footer ">
                            <button class="btn btn-primary mr-1" type="submit">{{ __('employee.update') }}</button>
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
<script src="{{ asset('assets/modules/summernote/summernote-bs4.js') }}"></script>
<script src="{{ asset('assets/modules/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('js/employee/edit.js') }}"></script>
@endsection
