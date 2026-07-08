@extends(auth()->check() ? 'admin.layouts.master' : 'admin.layouts.public-master')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/modules/select2/dist/css/select2.min.css') }}">
@endsection

@section('main-content')
<section class="section">
    <div class="section-header">
        <h1>Carga de Data Interna</h1>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Carga Masiva de Funcionarios</h4>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">
                            Utilice esta opción para registrar múltiples funcionarios internos en una sola carga.
                        </p>
                        <form method="POST" action="{{ route('admin.internal-staff-data.store-massive') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label for="massive_file">Archivo masivo (CSV / XLSX) <span class="text-danger">*</span></label>
                                <div class="custom-file">
                                    <input type="file" id="massive_file" name="massive_file"
                                        class="custom-file-input @error('massive_file') is-invalid @enderror"
                                        accept=".csv,.txt,.xlsx,.xls">
                                    <label class="custom-file-label" for="massive_file">Seleccione un archivo</label>
                                </div>
                                @error('massive_file')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-file-upload"></i> Cargar archivo masivo
                            </button>
                            <a href="{{ route('admin.internal-staff-data.download-template') }}" class="btn btn-outline-primary btn-block mt-2">
                                <i class="fas fa-file-excel"></i> Plantilla de carga masiva
                            </a>
                        </form>
                    </div>
                </div>

                <div class="card border-left-warning">
                    <div class="card-body">
                        <h6 class="mb-2"><i class="fas fa-info-circle text-warning"></i> Información Importante</h6>
                        <p class="mb-0 text-muted">
                            Asegúrese de usar el formato de "plantilla de carga masiva" validado para carga masiva, asegurando que los datos esten correctos.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card">
                    <form method="POST" action="{{ route('admin.internal-staff-data.store') }}" id="internal-staff-individual-form">
                        @csrf
                        <div class="card-header">
                            <h4>Portal de Carga de Datos de personal SENIAT</h4>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-light border">
                                Asegurese que los datos sean los correctos para el personal SENIAT
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="cedula">Cédula de Identidad <span class="text-danger">*</span></label>
                                    <input id="cedula" type="text" name="cedula"
                                        class="form-control @error('cedula') is-invalid @enderror"
                                        value="{{ old('cedula') }}" placeholder="V-12.345.678">
                                    @error('cedula')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="extension_telefonica">Extensión Telefónica</label>
                                    <input id="extension_telefonica" type="text" name="extension_telefonica"
                                        class="form-control @error('extension_telefonica') is-invalid @enderror"
                                        value="{{ old('extension_telefonica') }}" placeholder="8888">
                                    @error('extension_telefonica')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="nombres">Nombres <span class="text-danger">*</span></label>
                                    <input id="nombres" type="text" name="nombres"
                                        class="form-control @error('nombres') is-invalid @enderror"
                                        value="{{ old('nombres') }}" placeholder="Juan Alberto">
                                    @error('nombres')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="apellidos">Apellidos <span class="text-danger">*</span></label>
                                    <input id="apellidos" type="text" name="apellidos"
                                        class="form-control @error('apellidos') is-invalid @enderror"
                                        value="{{ old('apellidos') }}" placeholder="Pérez González">
                                    @error('apellidos')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label for="email_institucional">Correo Electrónico Institucional</label>
                                    <input id="email_institucional" type="email" name="email_institucional"
                                        class="form-control @error('email_institucional') is-invalid @enderror"
                                        value="{{ old('email_institucional') }}" placeholder="usuario@seniat.gob.ve">
                                    @error('email_institucional')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="region_id">Región / Dependencia <span class="text-danger">*</span></label>
                                    <select id="region_id" name="region_id"
                                        class="form-control select2 @error('region_id') is-invalid @enderror">
                                        <option value="">Seleccione una región</option>
                                        @foreach($regions as $region)
                                            <option value="{{ $region->id }}" {{ old('region_id') == $region->id ? 'selected' : '' }}>
                                                {{ $region->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('region_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="headquarters_id">Sede <span class="text-danger">*</span></label>
                                    <select id="headquarters_id" name="headquarters_id"
                                        class="form-control select2 @error('headquarters_id') is-invalid @enderror">
                                        <option value="">Seleccione una sede</option>
                                        @foreach($headquarters as $headquarter)
                                            <option value="{{ $headquarter->id }}"
                                                data-region="{{ $headquarter->region_id }}"
                                                {{ old('headquarters_id') == $headquarter->id ? 'selected' : '' }}>
                                                {{ $headquarter->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small id="headquarters-help" class="form-text text-muted"></small>
                                    @error('headquarters_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="designation_id">Cargo Actual</label>
                                    <select id="designation_id" name="designation_id"
                                        class="form-control select2 @error('designation_id') is-invalid @enderror">
                                        <option value="">Seleccione un cargo</option>
                                        @foreach($designations as $designation)
                                            <option value="{{ $designation->id }}" {{ old('designation_id') == $designation->id ? 'selected' : '' }}>
                                                {{ $designation->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Al seleccionar un cargo se cargará su detalle por GET.</small>
                                    @error('designation_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="department_id">Gerencia / Oficina Adscrita</label>
                                    <select id="department_id" name="department_id"
                                        class="form-control select2 @error('department_id') is-invalid @enderror">
                                        <option value="">Seleccione una gerencia/oficina</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Al seleccionar una gerencia se mostrará su información expandida.</small>
                                    @error('department_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-6">
                                    <div id="designation-details" class="card border-primary d-none">
                                        <div class="card-header py-2">
                                            <h6 class="mb-0"><i class="fas fa-layer-group"></i> Detalle del Cargo</h6>
                                        </div>
                                        <div class="card-body py-3">
                                            <p class="mb-1"><strong>Nombre:</strong> <span data-field="name">-</span></p>
                                            <p class="mb-1"><strong>Sede:</strong> <span data-field="headquarters">-</span></p>
                                            <p class="mb-0"><strong>Estatus:</strong> <span data-field="status">-</span></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div id="department-details" class="card border-info d-none">
                                        <div class="card-header py-2">
                                            <h6 class="mb-0"><i class="fas fa-building"></i> Detalle de Gerencia / Oficina</h6>
                                        </div>
                                        <div class="card-body py-3">
                                            <p class="mb-1"><strong>Nombre:</strong> <span data-field="name">-</span></p>
                                            <p class="mb-1"><strong>Región:</strong> <span data-field="region">-</span></p>
                                            <p class="mb-0"><strong>Estatus:</strong> <span data-field="status">-</span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label for="observaciones">Observaciones</label>
                                    <textarea id="observaciones" name="observaciones" rows="4"
                                        class="form-control @error('observaciones') is-invalid @enderror"
                                        placeholder="Información complementaria de interés para el trámite.">{{ old('observaciones') }}</textarea>
                                    @error('observaciones')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Registrar funcionario
                            </button>
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
@endsection

@push('js')
<script>
    (function () {
        const designationSelect = $('#designation_id');
        const departmentSelect = $('#department_id');
        const regionSelect = $('#region_id');
        const headquartersSelect = $('#headquarters_id');
        const headquartersHelp = document.getElementById('headquarters-help');
        const individualForm = document.getElementById('internal-staff-individual-form');
        const designationDetails = document.getElementById('designation-details');
        const departmentDetails = document.getElementById('department-details');
        const designationBaseUrl = "{{ route('admin.internal-staff-data.designation.get', ['designation' => 'ID_PLACEHOLDER'], false) }}".replace('/ID_PLACEHOLDER', '');
        const departmentBaseUrl = "{{ route('admin.internal-staff-data.department.get', ['department' => 'ID_PLACEHOLDER'], false) }}".replace('/ID_PLACEHOLDER', '');
        const headquartersPlaceholder = headquartersSelect.find('option:first').text() || 'Seleccione una sede';
        const headquartersOptions = headquartersSelect.find('option[value!=""]').map(function () {
            const option = $(this);
            return {
                value: String(option.val()),
                label: option.text(),
                region: String(option.data('region') || '')
            };
        }).get();

        function fillDetails(container, data) {
            const nameField = container.querySelector('[data-field="name"]');
            const headquartersField = container.querySelector('[data-field="headquarters"]');
            const regionField = container.querySelector('[data-field="region"]');
            const statusField = container.querySelector('[data-field="status"]');

            if (nameField) {
                nameField.textContent = data.name || '-';
            }
            if (headquartersField) {
                headquartersField.textContent = data.headquarters || 'Sin sede asignada';
            }
            if (regionField) {
                regionField.textContent = data.region || 'Sin región asignada';
            }
            if (statusField) {
                statusField.textContent = data.status || '-';
            }
        }


        function hideDetails(container) {
            container.classList.add('d-none');
            fillDetails(container, { name: '-', headquarters: '-', region: '-', status: '-' });
        }

        function fetchDesignationDetails(id) {
            if (!id) {
                hideDetails(designationDetails);
                return;
            }

            fetch(designationBaseUrl + '/' + id, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                fillDetails(designationDetails, data);
                designationDetails.classList.remove('d-none');
            })
            .catch(() => hideDetails(designationDetails));
        }

        function fetchDepartmentDetails(id) {
            if (!id) {
                hideDetails(departmentDetails);
                return;
            }

            fetch(departmentBaseUrl + '/' + id, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                fillDetails(departmentDetails, data);
                departmentDetails.classList.remove('d-none');
            })
            .catch(() => hideDetails(departmentDetails));
        }

        designationSelect.on('change', function () {
            fetchDesignationDetails($(this).val());
        });

        departmentSelect.on('change', function () {
            fetchDepartmentDetails($(this).val());
        });

        function filterHeadquartersByRegion() {
            const selectedRegionId = String(regionSelect.val() || '');
            const currentHeadquarters = String(headquartersSelect.val() || '');
            const filteredOptions = headquartersOptions.filter(function (option) {
                return selectedRegionId && option.region === selectedRegionId;
            });

            headquartersSelect.empty();
            headquartersSelect.append(new Option(headquartersPlaceholder, ''));

            filteredOptions.forEach(function (option) {
                headquartersSelect.append(new Option(option.label, option.value));
            });

            const hasCurrentSelection = filteredOptions.some(function (option) {
                return option.value === currentHeadquarters;
            });

            if (hasCurrentSelection) {
                headquartersSelect.val(currentHeadquarters);
            } else {
                headquartersSelect.val('');
            }

            const hasHeadquarters = filteredOptions.length > 0;
            headquartersSelect.prop('disabled', !hasHeadquarters);

            if (headquartersHelp) {
                if (!selectedRegionId) {
                    headquartersHelp.textContent = 'Seleccione primero una región / dependencia.';
                } else if (!hasHeadquarters) {
                    headquartersHelp.textContent = 'Esta región no tiene sedes asociadas; puede registrar sin sede.';
                } else {
                    headquartersHelp.textContent = 'Seleccione la sede correspondiente a la región indicada.';
                }
            }

            headquartersSelect.trigger('change.select2');
        }

        regionSelect.on('change', filterHeadquartersByRegion);

        if (individualForm) {
            individualForm.addEventListener('submit', function (event) {
                headquartersSelect.prop('disabled', false);

                const selectedRegionId = String(regionSelect.val() || '');
                const selectedHeadquarters = String(headquartersSelect.val() || '');
                const availableForRegion = headquartersOptions.filter(function (option) {
                    return option.region === selectedRegionId;
                });

                if (selectedRegionId && availableForRegion.length > 0 && !selectedHeadquarters) {
                    event.preventDefault();
                    alert('Debe seleccionar una sede válida para la región indicada.');
                }
            });
        }

        if (designationSelect.val()) {
            fetchDesignationDetails(designationSelect.val());
        }
        if (departmentSelect.val()) {
            fetchDepartmentDetails(departmentSelect.val());
        }
        filterHeadquartersByRegion();
    })();
</script>
@endpush
