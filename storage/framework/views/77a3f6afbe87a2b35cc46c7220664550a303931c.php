<?php $__env->startSection('main-content'); ?>

<section class="section">
    <div class="section-header">
        <h1><?php echo e(__('employee.employees')); ?></h1>
        <?php echo e(Breadcrumbs::render('employees')); ?>

    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">

                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['employees_create', 'employees_create_headquarters'])): ?>
                        <div class="card-header">
                            <a href="<?php echo e(route('admin.employees.create')); ?>" class="btn btn-icon icon-left btn-primary"><i
                                    class="fas fa-plus"></i> <?php echo e(__('employee.add_employee')); ?></a>
                        </div>
                    <?php endif; ?>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="maintable"
                                data-url="<?php echo e(route('admin.employees.get-employees')); ?>"
                                data-status="<?php echo e(\App\Enums\Status::ACTIVE); ?>" data-hidecolumn="<?php echo e(auth()->user()->can('employees_show') || auth()->user()->can('employees_edit') || auth()->user()->can('employees_delete')); ?>">
                                <thead>
                                    <tr>
                                        <th><?php echo e(__('levels.id')); ?></th>
                                        <th><?php echo e(__('levels.image')); ?></th>
                                        <th><?php echo e(__('levels.name')); ?></th>
                                        <th><?php echo e(__('levels.email')); ?></th>
                                        <th><?php echo e(__('levels.phone')); ?></th>
                                        <th><?php echo e(__('region.region')); ?></th>
                                        <th><?php echo e(__('headquarters.headquarters')); ?></th>
                                        <th><?php echo e(__('employee.joining_date')); ?></th>
                                        <th><?php echo e(__('levels.status')); ?></th>
                                        <th><?php echo e(__('levels.actions')); ?></th>
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

<?php $__env->stopSection(); ?>



<?php $__env->startSection('css'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/modules/datatables.net-bs4/css/dataTables.bootstrap4.min.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('assets/modules/datatables.net-select-bs4/css/select.bootstrap4.min.css')); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script src="<?php echo e(asset('assets/modules/datatables/media/js/jquery.dataTables.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/modules/datatables.net-bs4/js/dataTables.bootstrap4.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/modules/datatables.net-select-bs4/js/select.bootstrap4.min.js')); ?>"></script>
<script src="<?php echo e(asset('js/employee/index.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/employee/index.blade.php ENDPATH**/ ?>