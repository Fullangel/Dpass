<?php $__env->startSection('main-content'); ?>

  <section class="section">
        <div class="section-header">
            <h1><?php echo e(__('designation.designations')); ?></h1>
            <?php echo e(Breadcrumbs::render('designations')); ?>

        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['designations_create', 'designations_create_headquarters'])): ?>
                            <div class="card-header">
                                <a href="<?php echo e(route('admin.designations.create')); ?>" class="btn btn-icon icon-left btn-primary"><i class="fas fa-plus"></i> <?php echo e(__('designation.add_designations')); ?></a>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">

                            <div class="table-responsive">
                                <table class="table table-striped" id="maintable" data-url="<?php echo e(route('admin.designations.get-designations')); ?>" data-status="<?php echo e(\App\Enums\Status::ACTIVE); ?>" data-hidecolumn="<?php echo e(auth()->user()->can('designations_edit') || auth()->user()->can('designations_edit_headquarters') || auth()->user()->can('designations_delete') || auth()->user()->can('designations_delete_headquarters')); ?>">
                                    <thead>
                                        <tr>
                                            <th><?php echo e(__('levels.id')); ?></th>
                                            <th><?php echo e(__('levels.name')); ?></th>
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
    <script src="<?php echo e(asset('js/designation/index.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/designation/index.blade.php ENDPATH**/ ?>