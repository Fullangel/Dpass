<?php $__env->startSection('main-content'); ?>

	<section class="section">
        <div class="section-header">
            <h1><?php echo e(__('menu.headquarters')); ?></h1>
            <?php echo e(Breadcrumbs::render('headquarters/edit')); ?>

        </div>

        <div class="section-body">
        	<div class="row">
				<div class="col-12 col-md-6 col-lg-6">
					<div class="card">
						<form action="<?php echo e(route('admin.headquarters.update', $headquarter->id)); ?>" method="POST">
							<?php echo csrf_field(); ?>
							<?php echo method_field('PUT'); ?>
							<div class="card-body">
								<div class="form-group">
									<label><?php echo e(__('levels.name')); ?></label> <span class="text-danger">*</span>
									<input type="text" name="name" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('name', $headquarter->name)); ?>">
									<?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
										<div class="invalid-feedback">
											<?php echo e($message); ?>

										</div>
									<?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
								</div>
							
							<div class="form-group">
								<label><?php echo e(__('levels.region')); ?></label> <span class="text-danger">*</span>
								<select name="region_id" class="form-control <?php $__errorArgs = ['region_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
									<option value=""><?php echo e(__('levels.select_region')); ?></option>
									<?php $__currentLoopData = $regions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $region): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
										<option value="<?php echo e($region->id); ?>" <?php echo e(old('region_id', $headquarter->region_id) == $region->id ? 'selected' : ''); ?>><?php echo e($region->name); ?></option>
									<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
								</select>
								<?php $__errorArgs = ['region_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
									<div class="invalid-feedback">
										<?php echo e($message); ?>

									</div>
								<?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
							</div>
							
							<div class="form-group">
								<label><?php echo e(__('levels.dependency')); ?></label>
								<select name="dependency_id" id="dependency_id" class="form-control <?php $__errorArgs = ['dependency_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
									<option value=""><?php echo e(__('levels.select_dependency')); ?></option>
									<?php $__currentLoopData = $dependencies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dependency): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
										<option value="<?php echo e($dependency->id); ?>" <?php echo e(old('dependency_id', $headquarter->dependency_id) == $dependency->id ? 'selected' : ''); ?>><?php echo e($dependency->name); ?></option>
									<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
								</select>
								<?php $__errorArgs = ['dependency_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
									<div class="invalid-feedback">
										<?php echo e($message); ?>

									</div>
								<?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
							</div>
							
							<div class="form-group">
								<label><?php echo e(__('levels.description')); ?></label>
								<textarea name="description" class="form-control <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" rows="3"><?php echo e(old('description', $headquarter->description)); ?></textarea>
								<?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
									<div class="invalid-feedback">
										<?php echo e($message); ?>

									</div>
								<?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
							</div>
							
							<div class="form-group">
								<label><?php echo e(__('levels.address')); ?></label>
								<textarea name="address" class="form-control <?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" rows="2"><?php echo e(old('address', $headquarter->address)); ?></textarea>
								<?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
									<div class="invalid-feedback">
										<?php echo e($message); ?>

									</div>
								<?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
							</div>
							
							<div class="form-group">
								<label><?php echo e(__('levels.phone')); ?></label>
								<input type="text" name="phone" class="form-control <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('phone', $headquarter->phone)); ?>">
								<?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
									<div class="invalid-feedback">
										<?php echo e($message); ?>

									</div>
								<?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
							</div>
						</div>

						<div class="card-footer">
							<button class="btn btn-primary mr-1" type="submit"><?php echo e(__('levels.update')); ?></button>
						</div>
					</form>
				</div>
			</div>
		</div>
        </div>
    </section>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('js'); ?>
<script>
$(document).ready(function() {
    // Función para cargar dependencias por región
    function loadDependenciesByRegion(regionId, selectedDependencyId = null) {
        if (!regionId) {
            $('#dependency_id').html('<option value=""><?php echo e(__('levels.select_dependency')); ?></option>');
            return;
        }

        // Deshabilitar el select mientras carga
        $('#dependency_id').prop('disabled', true);
        
        $.ajax({
            url: '<?php echo e(route("admin.headquarters.get-dependencies-by-region")); ?>',
            type: 'GET',
            data: { region_id: regionId },
            timeout: 10000, // 10 segundos de timeout
            success: function(data) {
                var options = '<option value=""><?php echo e(__('levels.select_dependency')); ?></option>';
                
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
                $('#dependency_id').html('<option value=""><?php echo e(__('levels.select_dependency')); ?></option>').prop('disabled', false);
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
    var currentDependencyId = <?php echo e($headquarter->dependency_id ?? 'null'); ?>;
    if (initialRegionId) {
        loadDependenciesByRegion(initialRegionId, currentDependencyId);
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('admin.layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/headquarters/edit.blade.php ENDPATH**/ ?>