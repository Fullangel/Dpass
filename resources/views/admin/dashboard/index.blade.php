@extends('admin.layouts.master')



@section('main-content')
<section class="section">
    <div class="section-header">
        <h1>{{ __('dashboard.dashboard') }}</h1>
        {{ Breadcrumbs::render('dashboard') }}
    </div>
    <div class="row">
        <div class="col-md-12">
            @if(!blank($attendance))
            <div class="float-right  d-flex text-center" style="margin-left:auto">
                <p class="mr-2">
                    <span class="clock-span"><i class="fas fa-4x fa-clock"></i> {{ date('g:i A') }}</span><br>
                    @if($attendance->checkin_time)
                    <span class="text-success">
                        {{ __('dashboard.clock_in_at') }} - {{$attendance->checkin_time}}
                        @if($attendance->checkout_time) <span class="text-danger ml-2">
                            {{ __('dashboard.clock_out_at') }} - {{$attendance->checkout_time}}</span>@endif
                    </span>
                    @endif
                </p>
                @if(!$attendance->checkout_time)
                <form action="{{ route('admin.attendance.clockout')}}" method="post">
                    {{ csrf_field() }}
                    <button class="btn  d-flex inputbtnclockout align-items-center btn-dark" type="submit"><i
                            class="fas fa-4x fa-sign-out-alt"></i>{{ __('dashboard.clock_out') }}</button>
                </form>
                @endif
            </div>
            @else
            <div class="float-right  d-flex text-center" style="margin-left:auto">
                <p class="mt-2 mr-2">
                    <span class="clock-span"><i class="fas fa-4x fa-clock"></i> {{ date('g:i A') }}</span><br>
                </p>
                <button type="button" class="btn  d-flex inputbtnclockin align-items-center btn-success"
                    data-toggle="modal" data-target="#exampleModal"><i
                        class="fas fa-4x fa-sign-out-alt"></i>{{ __('dashboard.clock_in') }}</button>
            </div>
            @endif
        </div>
    </div>

    @if(auth()->user()->getrole->name == 'Employee')
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-12">
            <a href="{{ route('admin.visitors.index') }}">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Visitantes Registrados</h4>
                        </div>
                        <div class="card-body">
                            {{$totalVisitor}}
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6 col-12">
            <a href="{{ route('admin.pre-registers.index') }}">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-warning">
                        <i class="fas fa-user-secret"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>A ver si cambia</h4>
                        </div>
                        <div class="card-body">
                            {{$totalPrerigister}}
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
    @else
    <div class="row">
        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
            <a href="{{ route('admin.employees.index') }}">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-danger">
                        <i class="far fa-user"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Vistantes Entrando</h4>
                        </div>
                        <div class="card-body">
                            {{$visitors_standby}}
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
            <a href="{{ route('admin.visitors.index') }}">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Vistantes En Instalaciones</h4>
                        </div>
                        <div class="card-body">
                            {{$visitors_in}}
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
            <a href="{{ route('admin.pre-registers.index') }}">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-warning">
                        <i class="fas fa-user-secret"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Visitas Terminadas </h4>
                        </div>
                        <div class="card-body">
                            {{$visitors_out}}
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
    @endif

    <div class="row">
        
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('dashboard.visitors') }} <span class="badge badge-primary">{{$totalVisitor}}</span></h4>
                </div>
                <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="maintable"
                                data-url="{{ route('admin.visitors.get-visitors') }}"
                                data-status="{{ \App\Enums\Status::ACTIVE }}" data-hidecolumn="{{ auth()->user()->can('visitors_show') || auth()->user()->can('visitors_edit') || auth()->user()->can('visitors_delete') }}">
                                <thead>
                                    <tr>
                                        <th>{{ __('levels.id') }}</th>
                                        <th>{{ __('levels.image') }}</th>
                                        <th>{{ __('visitor.national_identification_no') }}</th>
                                        <th>{{ __('levels.name') }}</th>
                                        <th>{{ __('visitor.employee') }}</th>
                                        <th>{{ __('visitor.checkin') }}</th>
                                        <th>{{ __('visitor.check_out') }}</th>
                                        <th>{{ __('levels.status') }}</th>
                                        <th class="col-md-3">{{ __('levels.actions') }}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
            </div>
        </div>
        
    </div>
</section>
<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{ __('dashboard.clock_in') }} - <span
                        class="clock-span"><i class="fas fa-4x fa-clock"></i> {{ date('g:i A') }}</span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.attendance.clockin') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{ __('dashboard.working_from') }}</label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                            value="{{ old('title') }}" placeholder="e.g. Office, Home, etc.">
                        @error('title')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-dismiss="modal">{{ __('dashboard.close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('dashboard.clock_in') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('assets/modules/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/modules/datatables.net-select-bs4/css/select.bootstrap4.min.css') }}">
@endsection

@section('scripts')
<script src="{{ asset('assets/modules/datatables/media/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/modules/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('assets/modules/datatables.net-select-bs4/js/select.bootstrap4.min.js') }}"></script>
<script src="{{ asset('js/visitor/index.js') }}"></script>
@endsection
