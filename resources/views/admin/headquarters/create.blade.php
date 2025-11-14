@extends('admin.layouts.master')

@section('main-content')

	<section class="section">
        <div class="section-header">
            <h1>{{ __('menu.headquarters') }}</h1>
            {{ Breadcrumbs::render('headquarters/add') }}
        </div>

        <div class="section-body">
        	<div class="row">
				<div class="col-12 col-md-6 col-lg-6">
					<div class="card">
						<form action="{{ route('admin.headquarters.store') }}" method="POST">
							@csrf
							<div class="card-body">
								<div class="form-group">
									<label>{{ __('levels.name') }}</label> <span class="text-danger">*</span>
									<input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
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
										<option value="{{ $region->id }}" {{ old('region_id') == $region->id ? 'selected' : '' }}>{{ $region->name }}</option>
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
								<select name="dependency_id" id="dependency_id" class="form-control @error('dependency_id') is-invalid @enderror" disabled>
									<option value="">{{ __('levels.select_region_first') }}</option>
								</select>
								@error('dependency_id')
									<div class="invalid-feedback">
										{{ $message }}
									</div>
								@enderror
							</div>
							
							<div class="form-group">
								<label>{{ __('levels.description') }}</label>
								<textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
								@error('description')
									<div class="invalid-feedback">
										{{ $message }}
									</div>
								@enderror
							</div>
							
							<div class="form-group">
								<label>{{ __('levels.address') }}</label>
								<textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="2">{{ old('address') }}</textarea>
								@error('address')
									<div class="invalid-feedback">
										{{ $message }}
									</div>
								@enderror
							</div>
							
							<div class="form-group">
								<label>{{ __('levels.phone') }}</label>
								<input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">
								@error('phone')
									<div class="invalid-feedback">
										{{ $message }}
									</div>
								@enderror
							</div>
						</div>

						<div class="card-footer">
							<button class="btn btn-primary mr-1" type="submit">{{ __('levels.submit') }}</button>
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
    function loadDependenciesByRegion(regionId) {
        if (!regionId) {
            $('#dependency_id').html('<option value="">{{ __('levels.select_region_first') }}</option>').prop('disabled', true);
            return;
        }

        // Deshabilitar el select mientras carga
        $('#dependency_id').prop('disabled', true).html('<option value="">{{ __('levels.loading_dependencies') }}</option>');
        
        $.ajax({
            url: '{{ route("admin.headquarters.get-dependencies-by-region") }}',
            type: 'GET',
            data: { region_id: regionId },
            timeout: 10000, // 10 segundos de timeout
            success: function(data) {
                var options = '<option value="">{{ __('levels.select_dependency') }}</option>';
                
                // Validar que data sea un array
                if (data && Array.isArray(data) && data.length > 0) {
                    $.each(data, function(index, dependency) {
                        if (dependency && dependency.id && dependency.name) {
                            options += '<option value="' + dependency.id + '">' + dependency.name + '</option>';
                        }
                    });
                } else {
                    options = '<option value="">{{ __('levels.no_dependencies_for_region') }}</option>';
                }
                
                $('#dependency_id').html(options).prop('disabled', false);
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar dependencias:', error);
                $('#dependency_id').html('<option value="">{{ __('levels.error_loading_dependencies') }}</option>').prop('disabled', false);
            }
        });
    }

    // Evento cuando cambia la región
    $('select[name="region_id"]').on('change', function() {
        var regionId = $(this).val();
        loadDependenciesByRegion(regionId);
    });

    // Inicializar: si hay una región seleccionada, cargar sus dependencias
    var initialRegionId = $('select[name="region_id"]').val();
    if (initialRegionId) {
        loadDependenciesByRegion(initialRegionId);
    }
});
</script>
@endpush