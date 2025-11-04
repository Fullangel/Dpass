<?php $__env->startSection('main-content'); ?>

  <section class="section">
        <div class="section-header">
            <h1><?php echo e(__('menu.headquarters')); ?></h1>
            <?php echo e(Breadcrumbs::render('headquarters')); ?>

        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('headquarters_create')): ?>
                            <div class="card-header">
                                <a href="<?php echo e(route('admin.headquarters.create')); ?>" class="btn btn-icon icon-left btn-primary"><i class="fas fa-plus"></i> <?php echo e(__('headquarters.add_headquarters')); ?></a>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">

                            <div class="table-responsive">
                                <table class="table table-striped" id="maintable" data-url="<?php echo e(route('admin.headquarters.get-headquarters')); ?>" data-hidecolumn="<?php echo e(auth()->user()->can('headquarters_edit') || auth()->user()->can('headquarters_delete')); ?>">
                                    <thead>
                                        <tr>
                                            <th><?php echo e(__('levels.id')); ?></th>
                                            <th><?php echo e(__('levels.name')); ?></th>
                                            <th><?php echo e(__('levels.region')); ?></th>
                                            <th><?php echo e(__('levels.description')); ?></th>
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
    <link rel="stylesheet" href="<?php echo e(asset('assets/modules/datatables/media/css/jquery.dataTables.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/modules/datatables.net-bs4/css/dataTables.bootstrap4.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/modules/datatables.net-select-bs4/css/select.bootstrap4.min.css')); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <script src="<?php echo e(asset('assets/modules/datatables/media/js/jquery.dataTables.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/modules/datatables.net-bs4/js/dataTables.bootstrap4.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/modules/datatables.net-select-bs4/js/select.bootstrap4.min.js')); ?>"></script>
    <script src="<?php echo e(asset('js/headquarters/index.js')); ?>"></script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/headquarters/index.blade.php ENDPATH**/ ?>