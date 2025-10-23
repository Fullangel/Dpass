@extends('admin.layouts.master')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/modules/select2/dist/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/modules/bootstrap-social/bootstrap-social.css') }}">
<link rel="stylesheet" href="{{ asset('assets/modules/summernote/summernote-bs4.css') }}">
<link rel="stylesheet" href="{{ asset('assets/modules/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}">
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Filter headquarters based on selected region
    function filterHeadquarters() {
        var selectedRegion = $('#region_id').val();
        var headquartersSelect = $('#headquarters_id');
        
        headquartersSelect.find('option').each(function() {
            var option = $(this);
            if (option.val() === '') {
                // Keep the "Select Headquarters" option
                option.show();
            } else {
                var regionId = option.data('region');
                if (selectedRegion === '' || regionId == selectedRegion) {
                    option.show();
                } else {
                    option.hide();
                }
            }
        });
        
        // Reset headquarters selection if current selection is hidden
        var selectedHeadquarters = headquartersSelect.val();
        if (selectedHeadquarters && headquartersSelect.find('option:selected').is(':hidden')) {
            headquartersSelect.val('');
        }
    }
    
    // Initial filter
    filterHeadquarters();
    
    // Filter on region change
    $('#region_id').change(function() {
        filterHeadquarters();
    });
});
</script>
@endpush

@section('main-content')

<section class="section">
    <div class="section-header">
        <h1>{{ __('employee.employees') }}</h1>
        {{ Breadcrumbs::render('employees/add') }}
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12 col-md-12 col-lg-12">
                <div class="card">
                    <form action="{{ route('admin.employees.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-group col">
                                    <label for="first_name">{{ __('employee.first_name') }}</label> <span
                                        class="text-danger">*</span>
                                    <input id="first_name" type="text" name="first_name"
                                        class="form-control {{ $errors->has('first_name') ? " is-invalid " : '' }}"
                                        value="{{ old('first_name') }}">
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
                                        value="{{ old('last_name') }}">
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
                                        value="{{ old('email') }}">
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
                                        value="{{ old('phone') }}">
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
                                        value="{{ old('date_of_joining') }}">
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
                                        <option value="{{ $key }}" {{ (old('gender') == $key) ? 'selected' : '' }}>
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
                                            {{ (old('department_id') == $department->id) ? 'selected' : '' }}>
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
                                            {{ (old('designation_id') == $designation->id) ? 'selected' : '' }}>
                                            {{ $designation->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('designation_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col">
                                    <label for="region_id">{{ __('region.region') }} <span class="text-danger">*</span></label>
                                    <select id="region_id" name="region_id"
                                        class="form-control @error('region_id') is-invalid @enderror" required
                                        @if(isset($is_supervisor) && $is_supervisor) disabled @endif>
                                        <option value="">{{ __('region.select_region') }}</option>
                                        @foreach($regions as $region)
                                        <option value="{{ $region->id }}"
                                            @if(isset($is_supervisor) && $is_supervisor && $supervisor_region_id == $region->id) selected
                                            @elseif(old('region_id') == $region->id) selected @endif>
                                            {{ $region->name }}</option>
                                        @endforeach
                                    </select>
                                    @if(isset($is_supervisor) && $is_supervisor)
                                    <input type="hidden" name="region_id" value="{{ $supervisor_region_id }}">
                                    @endif
                                    @error('region_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class="form-group col">
                                    <label for="headquarters_id">{{ __('headquarters.headquarters') }} <span class="text-danger">*</span></label>
                                    <select id="headquarters_id" name="headquarters_id"
                                        class="form-control @error('headquarters_id') is-invalid @enderror" required
                                        @if(isset($is_supervisor) && $is_supervisor) disabled @endif>
                                        <option value="">{{ __('headquarters.select_headquarters') }}</option>
                                        @foreach($headquarters as $headquarter)
                                        <option value="{{ $headquarter->id }}" data-region="{{ $headquarter->region_id }}"
                                            @if(isset($is_supervisor) && $is_supervisor && $supervisor_headquarters_id == $headquarter->id) selected
                                            @elseif(old('headquarters_id') == $headquarter->id) selected @endif>
                                            {{ $headquarter->name }}</option>
                                        @endforeach
                                    </select>
                                    @if(isset($is_supervisor) && $is_supervisor)
                                    <input type="hidden" name="headquarters_id" value="{{ $supervisor_headquarters_id }}">
                                    @endif
                                    @error('headquarters_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col">
                                    <label>{{ __('employee.password') }}</label> <span class="text-danger">*</span>
                                    <input type="password" name="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        value="{{ old('password') }}">
                                    @error('password')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class="form-group col">
                                    <label>{{ __('employee.confirm_password') }}</label> <span
                                        class="text-danger">*</span>
                                    <input type="password" name="password_confirmation"
                                        class="form-control @error('password_confirmation') is-invalid @enderror"
                                        value="{{ old('password_confirmation') }}">
                                    @error('password_confirmation')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class="form-group col">
                                    <label>{{ __('levels.status') }}</label> <span class="text-danger">*</span>
                                    <select name="status" class="form-control @error('status') is-invalid @enderror">
                                        @foreach(trans('statuses') as $key => $status)
                                        <option value="{{ $key }}" {{ (old('status') == $key) ? 'selected' : '' }}>
                                            {{ $status }}</option>
                                        @endforeach
                                    </select>
                                    @error('status')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                @if($is_admin)
                                <div class="form-group col">
                                    <label for="role_id">{{ __('employee.role') }}</label> <span class="text-danger">*</span>
                                    <select id="role_id" name="role_id" class="form-control @error('role_id') is-invalid @enderror">
                                        <option value="">{{ __('employee.select_role') }}</option>
                                        @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ (old('role_id') == $role->id) ? 'selected' : '' }}>
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

                            <div class="form-row">
                                <div class="form-group col">
                                    <label for="about">{{ __('employee.about') }}</label>
                                    <textarea name="about" class="summernote-simple form-control height-textarea @error('about')
                                                  is-invalid @enderror" id="about">
                                    {{ old('about') }}
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
                                    <img class="img-thumbnail image-width mt-4 mb-3" id="previewImage"
                                        src="{{ asset('assets/img/default/user.png') }}" alt="your image" />
                                </div>
                            </div>
                        </div>

                        <div class="card-footer ">
                            <button class="btn btn-primary mr-1" type="submit">{{ __('employee.submit') }}</button>
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
<script src="{{ asset('js/employee/create.js') }}"></script>

@endsection
