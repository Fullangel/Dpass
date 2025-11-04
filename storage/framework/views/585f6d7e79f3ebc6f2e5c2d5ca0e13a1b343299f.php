<?php $__env->startSection('main-content'); ?>
<section class="section">
    <div class="section-header">
        <h1><?php echo e(__('dashboard.dashboard')); ?></h1>
        <?php echo e(Breadcrumbs::render('dashboard')); ?>

    </div>
    <div class="row">
        <div class="col-md-12">
            <?php if(!blank($attendance)): ?>
            <div class="float-right  d-flex text-center" style="margin-left:auto">
                <p class="mr-2">
                    <span class="clock-span"><i class="fas fa-4x fa-clock"></i> <?php echo e(date('g:i A')); ?></span><br>
                    <?php if($attendance->checkin_time): ?>
                    <span class="text-success">
                        <?php echo e(__('dashboard.clock_in_at')); ?> - <?php echo e($attendance->checkin_time); ?>

                        <?php if($attendance->checkout_time): ?> <span class="text-danger ml-2">
                            <?php echo e(__('dashboard.clock_out_at')); ?> - <?php echo e($attendance->checkout_time); ?></span><?php endif; ?>
                    </span>
                    <?php endif; ?>
                </p>
                <?php if(!$attendance->checkout_time): ?>
                <form action="<?php echo e(route('admin.attendance.clockout')); ?>" method="post">
                    <?php echo e(csrf_field()); ?>

                    <button class="btn  d-flex inputbtnclockout align-items-center btn-dark" type="submit"><i
                            class="fas fa-4x fa-sign-out-alt"></i><?php echo e(__('dashboard.clock_out')); ?></button>
                </form>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="float-right  d-flex text-center" style="margin-left:auto">
                <p class="mt-2 mr-2">
                    <span class="clock-span"><i class="fas fa-4x fa-clock"></i> <?php echo e(date('g:i A')); ?></span><br>
                </p>
                <button type="button" class="btn  d-flex inputbtnclockin align-items-center btn-success"
                    data-toggle="modal" data-target="#exampleModal"><i
                        class="fas fa-4x fa-sign-out-alt"></i><?php echo e(__('dashboard.clock_in')); ?></button>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if(auth()->user()->getrole->name == 'Employee'): ?>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-12">
            <a href="<?php echo e(route('admin.visitors.index')); ?>">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Visitantes Registrados</h4>
                        </div>
                        <div class="card-body">
                            <?php echo e($totalVisitor); ?>

                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6 col-12">
            <a href="<?php echo e(route('admin.pre-registers.index')); ?>">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-warning">
                        <i class="fas fa-user-secret"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>A ver si cambia</h4>
                        </div>
                        <div class="card-body">
                            <?php echo e($totalPrerigister); ?>

                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
    <?php else: ?>
    <div class="row">
        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
            <a href="<?php echo e(route('admin.employees.index')); ?>">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-danger">
                        <i class="far fa-user"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Vistantes Entrando</h4>
                        </div>
                        <div class="card-body">
                            <?php echo e($visitors_standby); ?>

                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
            <a href="<?php echo e(route('admin.visitors.index')); ?>">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Vistantes En Instalaciones</h4>
                        </div>
                        <div class="card-body">
                            <?php echo e($visitors_in); ?>

                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
            <a href="<?php echo e(route('admin.pre-registers.index')); ?>">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-warning">
                        <i class="fas fa-user-secret"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Visitas Terminadas </h4>
                        </div>
                        <div class="card-body">
                            <?php echo e($visitors_out); ?>

                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
    <?php endif; ?>

    <div class="row">
        
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4><?php echo e(__('dashboard.visitors')); ?> <span class="badge badge-primary"><?php echo e($totalVisitor); ?></span></h4>
                </div>
                <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="maintable"
                                data-url="<?php echo e(route('admin.visitors.get-visitors')); ?>"
                                data-status="<?php echo e(\App\Enums\Status::ACTIVE); ?>" data-hidecolumn="<?php echo e(auth()->user()->can('visitors_show') || auth()->user()->can('visitors_edit') || auth()->user()->can('visitors_delete')); ?>">
                                <thead>
                                    <tr>
                                        <th><?php echo e(__('levels.id')); ?></th>
                                        <th><?php echo e(__('levels.image')); ?></th>
                                        <th><?php echo e(__('visitor.national_identification_no')); ?></th>
                                        <th><?php echo e(__('levels.name')); ?></th>
                                        <th><?php echo e(__('visitor.employee')); ?></th>
                                        <th><?php echo e(__('visitor.location')); ?></th>
                                        <th><?php echo e(__('visitor.checkin')); ?></th>
                                        <th><?php echo e(__('visitor.check_out')); ?></th>
                                        <th><?php echo e(__('levels.status')); ?></th>
                                        <th class="col-md-3"><?php echo e(__('levels.actions')); ?></th>
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
                <h5 class="modal-title" id="exampleModalLabel"><?php echo e(__('dashboard.clock_in')); ?> - <span
                        class="clock-span"><i class="fas fa-4x fa-clock"></i> <?php echo e(date('g:i A')); ?></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?php echo e(route('admin.attendance.clockin')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label><?php echo e(__('dashboard.working_from')); ?></label>
                        <input type="text" name="title" class="form-control <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            value="<?php echo e(old('title')); ?>" placeholder="e.g. Office, Home, etc.">
                        <?php $__errorArgs = ['title'];
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
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-dismiss="modal"><?php echo e(__('dashboard.close')); ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo e(__('dashboard.clock_in')); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/modules/datatables.net-bs4/css/dataTables.bootstrap4.min.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('assets/modules/datatables.net-select-bs4/css/select.bootstrap4.min.css')); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script src="<?php echo e(asset('assets/modules/datatables/media/js/jquery.dataTables.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/modules/datatables.net-bs4/js/dataTables.bootstrap4.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/modules/datatables.net-select-bs4/js/select.bootstrap4.min.js')); ?>"></script>
<script src="<?php echo e(asset('js/visitor/index.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/admin/dashboard/index.blade.php ENDPATH**/ ?>