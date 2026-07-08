@extends('admin.layouts.master')

@section('main-content')
<section class="section">
    <div class="section-header">
        <h1>Cola de visitas por destino</h1>
    </div>

    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-primary">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Registrados hoy</h4>
                    </div>
                    <div class="card-body">
                        {{ $todayCount }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-warning">
                    <i class="fas fa-door-open"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>En instalaciones (sin salida)</h4>
                    </div>
                    <div class="card-body">
                        {{ $pendingCount }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($destinations->isNotEmpty())
        <div class="alert alert-info">
            Destinos asignados:
            @foreach($destinations as $destination)
                <span class="badge badge-light">{{ $destination->name }}</span>
            @endforeach
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Visitantes en camino</h4>
                    <div>
                        <label class="mb-0 mr-3">
                            <input type="checkbox" id="filter-today-only" checked> Solo hoy
                        </label>
                        <label class="mb-0">
                            <input type="checkbox" id="filter-inside-only"> Solo sin salida
                        </label>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="destination-queue-table"
                            data-url="{{ route('admin.visit-destination-queue.get-visits') }}">
                            <thead>
                                <tr>
                                    <th>{{ __('levels.id') }}</th>
                                    <th>{{ __('levels.image') }}</th>
                                    <th>{{ __('visitor.national_identification_no') }}</th>
                                    <th>{{ __('levels.name') }}</th>
                                    <th>Destino</th>
                                    <th>{{ __('visitor.purpose') }}</th>
                                    <th>Registrado</th>
                                    <th>{{ __('visitor.checkin') }}</th>
                                    <th>{{ __('visitor.check_out') }}</th>
                                    <th>{{ __('levels.status') }}</th>
                                    <th>{{ __('levels.actions') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('assets/modules/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/modules/datatables.net-select-bs4/css/select.bootstrap4.min.css') }}">
@endsection

@section('scripts')
<script src="{{ asset('assets/modules/datatables/media/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/modules/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('js/visit-destination-queue/index.js') }}"></script>
@endsection
