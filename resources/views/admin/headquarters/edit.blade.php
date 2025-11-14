@extends('admin.layouts.master')

@section('main-content')

	<section class="section">
        <div class="section-header">
            <h1>{{ __('menu.headquarters') }}</h1>
            {{ Breadcrumbs::render('headquarters/edit') }}
        </div>

        <div class="section-body">
        	<div class="row">
				<div class="col-12 col-md-6 col-lg-6">
					<div class="card">
						<form action="{{ route('admin.headquarters.update', $headquarter->id) }}" method="POST">
							@csrf
							@method('PUT')
							<div class="card-body">
								<div class="form-group">
									<label>{{ __('levels.name') }}</label> <span class="text-danger">*</span>
									<input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $headquarter->name) }}">
									@error('name')
										<div class="invalid-feedback">
											{{ $message }}
										</div>
									@enderror
								</div>
							
							<div class="form-group">
								<label>{{ __('levels.region') }}</label> <span class="text-danger">*</span>
								<select name="region_id" class="form-control @error('region_id') is-invalid @enderror">
									<option value="">{{ __('levels.select_region') }}</option>
									@foreach($regions as $region)
										<option value="{{ $region->id }}" {{ old('region_id', $headquarter->region_id) == $region->id ? 'selected' : '' }}>{{ $region->name }}</option>
									@endforeach
								</select>
								@error('region_id')
									<div class="invalid-feedback">
										{{ $message }}
									</div>
								@enderror
							</div>
							
							<div class="form-group">
								<label>{{ __('levels.dependency') }}</label>
								<select name="dependency_id" id="dependency_id" class="form-control @error('dependency_id') is-invalid @enderror">
									<option value="">{{ __('levels.select_dependency') }}</option>
									@foreach($dependencies as $dependency)
										<option value="{{ $dependency->id }}" {{ old('dependency_id', $headquarter->dependency_id) == $dependency->id ? 'selected' : '' }}>{{ $dependency->name }}</option>
									@endforeach
								</select>
								@error('dependency_id')
									<div class="invalid-feedback">
										{{ $message }}
									</div>
								@enderror
							</div>
							
							<div class="form-group">
								<label>{{ __('levels.description') }}</label>
								<textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $headquarter->description) }}</textarea>
								@error('description')
									<div class="invalid-feedback">
										{{ $message }}
									</div>
								@enderror
							</div>
							
							<div class="form-group">
								<label>{{ __('levels.address') }}</label>
								<textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="2">{{ old('address', $headquarter->address) }}</textarea>
								@error('address')
									<div class="invalid-feedback">
										{{ $message }}
									</div>
								@enderror
							</div>
							
							<div class="form-group">
								<label>{{ __('levels.phone') }}</label>
								<input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $headquarter->phone) }}">
								@error('phone')
									<div class="invalid-feedback">
										{{ $message }}
									</div>
								@enderror
							</div>
						</div>

						<div class="card-footer">
							<button class="btn btn-primary mr-1" type="submit">{{ __('levels.update') }}</button>
						</div>
					</form>
				</div>
			</div>
		</div>
        </div>
    </section>

@endsection

@push('js')
<script>
$(document).ready(function() {
    // Función para cargar dependencias por región
    function loadDependenciesByRegion(regionId, selectedDependencyId = null) {
        if (!regionId) {
            $('#dependency_id').html('<option value="">{{ __('levels.select_dependency') }}</option>');
            return;
        }

        // Deshabilitar el select mientras carga
        $('#dependency_id').prop('disabled', true);
        
        $.ajax({
            url: '{{ route("admin.headquarters.get-dependencies-by-region") }}',
            type: 'GET',
            data: { region_id: regionId },
            timeout: 10000, // 10 segundos de timeout
            success: function(data) {
                var options = '<option value="">{{ __('levels.select_dependency') }}</option>';
                
                // Validar que data sea un array
                if (data && Array.isArray(data)) {
                    $.each(data, function(index, dependency) {
                        if (dependency && dependency.id && dependency.name) {
                            var selected = (selectedDependencyId && selectedDependencyId === dependency.id) ? ' selected' : '';
                            options += '<option value="' + dependency.id + '"' + selected + '>' + dependency.name + '</option>';
                        }
                    });
                }
                
                $('#dependency_id').html(options).prop('disabled', false);
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar dependencias:', error);
                $('#dependency_id').html('<option value="">{{ __('levels.select_dependency') }}</option>').prop('disabled', false);
            }
        });
    }

    // Evento cuando cambia la región
    $('select[name="region_id"]').on('change', function() {
        var regionId = $(this).val();
        loadDependenciesByRegion(regionId);
    });

    // Si hay una región seleccionada al cargar la página, cargar sus dependencias
    var initialRegionId = $('select[name="region_id"]').val();
    var currentDependencyId = {{ $headquarter->dependency_id ?? 'null' }};
    if (initialRegionId) {
        loadDependenciesByRegion(initialRegionId, currentDependencyId);
    }
});
</script>
@endpush