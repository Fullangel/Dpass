@extends('admin.layouts.master')

@section('main-content')
<section class="section">
    <div class="section-header">
        <h1>Destinos de visita</h1>
        {{ Breadcrumbs::render('visit-destinations') }}
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    @if($canCreate)
                        <div class="card-header">
                            <a href="{{ route('admin.visit-destinations.create') }}" class="btn btn-icon icon-left btn-primary">
                                <i class="fas fa-plus"></i> Agregar destino
                            </a>
                        </div>
                    @endif
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="maintable"
                                data-url="{{ route('admin.visit-destinations.get-visit-destinations') }}"
                                data-hidecolumn="{{ $canEdit || $canDelete ? 1 : 0 }}">
                                <thead>
                                    <tr>
                                        <th>{{ __('levels.id') }}</th>
                                        <th>{{ __('levels.name') }}</th>
                                        <th>Sede</th>
                                        <th>Reglas</th>
                                        <th>Recepcionistas</th>
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
    </div>
</section>
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('assets/modules/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
@endsection

@section('scripts')
<script src="{{ asset('assets/modules/datatables/media/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/modules/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('js/visit-destination/index.js') }}"></script>
@endsection
