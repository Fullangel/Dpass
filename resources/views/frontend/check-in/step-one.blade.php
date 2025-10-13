@extends('frontend.layouts.frontend')

@section('content')
    <section id="pm-banner-1" class="">
        <div class="container">
            <div class="row custom-css-step-one">
                <div class="col-lg-6 p-0">
                    <div style="margin: 30px;">
                        <h3 class="form-title">{{ __('Datos de Visitante') }}</h3>
                        {!! Form::open(['route' => 'check-in.step-one.next', 'class' => 'form-horizontal', 'files' => true]) !!}
                        <div class="save">
                            <div class="visitor" id="visitor">
                                <div class="row">
                                    @if (@$visitor->image)
                                        <div class="col-md-12 col-sm-12 text-center mb-2">
                                            <img src="{{ $visitor->image }}" alt="Visitor Image" class="w-25">
                                        </div>
                                    @endif
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label class="label-css" for="first_name">{{ __('visitor.first_name') }} <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="first_name" id="first_name"
                                                class="form-control input-css @error('first_name') is-invalid @enderror"
                                                value="{{ isset($visitor->first_name) ? $visitor->first_name : old('first_name') }}"
                                                {{ isset($visitor->first_name) ? 'readonly' : '' }}>
                                            @error('first_name')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror

                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label class="label-css" for="">{{ __('visitor.last_name') }} <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="last_name" id="last_name"
                                                class="form-control input-css @error('last_name') is-invalid @enderror"
                                                value="{{ isset($visitor->last_name) ? $visitor->last_name : old('last_name') }}"
                                                {{ isset($visitor->last_name) ? 'readonly' : '' }}>
                                            @error('last_name')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">

                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group mt-1">
                                            <label class="label-css" for="employee_id">{{ __('visitor.select_employee') }}
                                                <span class="text-danger">*</span></label>
                                            <select id="employee_id" name="employee_id"
                                                class="form-control input-css js-select2 @error('employee_id') is-invalid @enderror">
                                                <option value="">{{ __('Select Employee') }}</option>
                                                @foreach ($employees as $key => $employee)
                                                    <option value="{{ $employee->id }}"
                                                        {{ old('employee_id', $employee_id) == $employee->id ? 'selected' : '' }}>
                                                        {{ $employee->name }} (
                                                        {{ optional($employee->department)->name }} )
                                                    </option>
                                                @endforeach
                                            </select>


                                            @error('employee_id')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label class="label-css" for="">{{ __('visitor.phone') }} <span
                                                    class="text-danger">*</span><span class="text-info"> (Con codigo
                                                    de pais,sin signo +)</span></label>
                                            <input type="text" name="phone" id="phone"
                                                class="form-control input-css @error('phone') is-invalid @enderror"
                                                value="{{ isset($visitor->phone) ? $visitor->phone : old('phone') }}" {{ isset($visitor->phone) ? 'readonly' : '' }}>
                                            @error('phone')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label class="label-css" for="">{{ __('visitor.email') }}</label>
                                            <input type="text" name="email" id="email"
                                                class="form-control input-css @error('email') is-invalid @enderror"
                                                value="{{ isset($visitor->email) ? $visitor->email : old('email') }}">
                                            @error('email')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group {{ $errors->has('gender') ? 'has-error' : '' }}">
                                            <label class="label-css" for="gender">{{ __('Género') }}</label>
                                            <select id="gender" name="gender"
                                                class="form-control input-css @error('gender') is-invalid @enderror"
                                                {{ isset($visitor->gender) ? 'readonly' : '' }}>
                                                @foreach (trans('genders') as $key => $gender)
                                                    <option value="{{ $key }}"
                                                        {{ (isset($visitor->gender) ? $visitor->gender : old('gender')) == $key ? 'selected' : '' }}>
                                                        {{ $gender }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('gender')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label class="label-css"
                                                for="company_name">{{ __('visitor.company_name') }}</label>
                                            <input type="text" name="company_name" id="company_name"
                                                class="form-control input-css @error('company_name') is-invalid @enderror"
                                                value="{{ isset($company_name) ? $company_name : old('company_name', isset($visitor->company_name) ? $visitor->company_name : '') }}">
                                            @error('company_name')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label class="label-css"
                                                for="national_identification_no">{{ __('visitor.national_identification_no') }}<span
                                                class="text-danger">*</span>
                                            </label>
                                            <input type="text" name="national_identification_no"
                                                id="national_identification_no"
                                                class="form-control input-css @error('national_identification_no') is-invalid @enderror"
                                                value="{{ isset($visitor->national_identification_no) ? $visitor->national_identification_no : old('national_identification_no') }}"
                                                {{ isset($visitor->national_identification_no) ? 'readonly' : '' }}>
                                            @error('national_identification_no')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">

                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group {{ $errors->has('purpose') ? 'has-error' : '' }}">
                                            <label class="label-css" for="purpose">{{ __('visitor.purpose') }} <span
                                                    class="text-danger">*</span></label>
                                            <textarea name="purpose" class="summernote-simple form-control height-textarea @error('purpose')is-invalid @enderror"
                                                id="purpose">{{ old('purpose') }}</textarea>
                                            @error('purpose')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">
                                            <label class="label-css" for="address">{{ __('visitor.address') }}</label>
                                            <textarea name="address" class="summernote-simple form-control height-textarea @error('address')is-invalid @enderror"
                                                id="address">{{ isset($visitor->address) ? $visitor->address : old('address') }}</textarea>
                                            @error('address')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                @if (setting('terms_visibility_status'))
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-check {{ $errors->has('accept_tc') ? 'has-error' : '' }}">
                                                <input type="checkbox" class="form-check-input" id="accept_tc"
                                                    name="accept_tc">
                                                <label class="form-check-label" for="accept_tc"> I agree to these <a
                                                        class="text-info" href="{{ route('terms_and_conditions.view') }}"
                                                        target="_blank">Terms and Conditions.</a></label>
                                            </div>
                                            @error('accept_tc')
                                                <div class="text-danger text-xs" id="terms_error">
                                                    {{ $errors->first('accept_tc') }}
                                                </div>
                                            @enderror
                                        </div>
                                    </div>
                                @endif
                                <div class="row mt-3">
                                    <div class="col-6">
                                        <a href="{{ route('check-in') }}" class="btn cancel-btn float-left">
                                            <span class="">{{ __('frontend.cancel') }}</span>
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <button type="submit" class="btn continue-btn float-right btn-submit-one"
                                            id="continue">
                                            {{ __('frontend.continue') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 image-display d-flex justify-content-end ">
                    <img class="img-css" src="{{ asset('frontend/images/vps.png') }}" alt="">
                </div>
            </div>
            <hr class="hr-line">
            <div class="d-flex justify-content-center footer-text pb-3">
                <span> {{ setting('site_footer') }}</span>
            </div>
        </div>
    </section>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/modules/select2/dist/css/select2.min.css') }}">
@endpush

@section('scripts')
    <script src="{{ asset('assets/modules/select2/dist/js/select2.full.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            $(".js-select2").select2({
                closeOnSelect: false
            });

            $(".btn-submit-one").attr("disabled", false);

            $(".btn-submit-one").click(function() {
                $(".btn-submit-one").attr("disabled", true);
                $('form').submit();
            });

        });
    </script>
@endsection
