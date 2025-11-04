<div class="navbar-bg"></div>
<nav class="navbar navbar-expand-lg main-navbar">
    <div class="form-inline mr-auto">
        <ul class="navbar-nav mr-3">
            <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars"></i></a></li>
        </ul>
    </div>
    <ul class="navbar-nav navbar-right">
        <li class=" hidecheck ">
            <form action="<?php echo e(route('admin.visitor.search')); ?>" method="post">
                <?php echo e(csrf_field()); ?>

                <div class="d-flex form-group  <?php echo e($errors->has('first_name') ? 'has-error' : ''); ?>" style="margin-bottom: -24px;margin-left:auto">
                    <input class="form-control inputid" style="margin-right: 5px;" type="text" name="visitorID" placeholder="<?php echo e(__('topbar_menu.enter_Visitor_id')); ?>">
                    <button class="btn  d-flex inputbtn align-items-center" type="submit"><i class="fas fa-4x fa-sign-out-alt"></i><?php echo e(__('topbar_menu.check_out')); ?></button>
                </div>
            </form>
        </li>
        <?php if(setting('front_end_enable_disable') == 1): ?>
        <li class="dropdown">
            <a data-toggle="tooltip" data-placement="bottom" title="Go to Frontend" href="<?php echo e(route('/')); ?>" class="nav-link nav-link-lg beep" target="_blank"><i class="fa fa-globe"></i></a>
        </li>
        <?php endif; ?>
        <li class="dropdown">
            <a href="" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                <?php if(!blank($language)): ?>
                <?php $__currentLoopData = $language; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(Session()->has('applocale') AND Session()->get('applocale') AND setting('locale')): ?>
                <?php if(Session()->get('applocale') == $lang->code): ?>
                <div class="d-sm-none d-lg-inline-block "><span class="flag-icon"><?php echo e($lang->flag_icon == null ? '🇬🇧' : $lang->flag_icon); ?></span><?php echo e($lang->name); ?></div>
                <?php endif; ?>
                <?php else: ?>
                <?php if(setting('locale') == $lang->code): ?>
                <div class="d-sm-none d-lg-inline-block "><span class="flag-icon"><?php echo e($lang->flag_icon == null ? '🇬🇧' : $lang->flag_icon); ?></span><?php echo e($lang->name); ?></div>
                <?php endif; ?>
                <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <?php if(!blank($language)): ?>
                <?php $__currentLoopData = $language; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('admin.lang.index',$lang->code)); ?>" class="dropdown-item has-icon">
                    <span class="flag-icon flag-icon-aw"><?php echo e($lang->flag_icon == null ? '🇬🇧' : $lang->flag_icon); ?> </span><?php echo e($lang->name); ?></a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            </div>
        </li>


        <?php if(auth()->user()->myrole == 2): ?>
        <?php if(!blank($latestVisitors)): ?>
        <li class="dropdown custom-visitor-notification">
            <a href="<?php echo e(route('admin.profile')); ?>" data-toggle="dropdown" class="dropdown-toggle custom-notification ">
                <div class=" d-lg-inline-block">
                    <i class="fas fa-bell"></i>
                    <div class="counter badge badge-pill badge-danger text-light">
                        <span class=" "><?php echo e(count($latestVisitors)); ?></span>
                    </div>
                </div>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <?php $__currentLoopData = $latestVisitors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $visitor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="notification-div p-2">
                    <div class="row no-gutters">
                        <div class="col-2">
                            <img src="<?php echo e($visitor->images); ?>" class="card-img notification-img">
                        </div>
                        <div class="col-10">
                            <div class="pl-2">
                                <p class="visitor-name"><?php echo e($visitor->visitor->name); ?></p>
                                <p class="visitor-purpose"><?php echo e(__('Purpose')); ?> : <?php echo e(Str::limit($visitor->purpose, 60)); ?></p>
                                <a class="btn btn-success btn-sm status-btn" href="<?php echo e(route('admin.visitor.change-status',[$visitor->id,2,true] )); ?>"><?php echo e(__('Accept')); ?></a>
                                <a class="btn  btn-danger btn-sm status-btn" href="<?php echo e(route('admin.visitor.change-status',[$visitor->id,3,true] )); ?>"><?php echo e(__('Reject')); ?></a>

                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </li>
        <?php else: ?>
        <li class="dropdown custom-visitor-notification">
            <div class=" d-lg-inline-block custom-notification">
                <i class="fas fa-bell"></i>
                <div class="counter badge badge-pill badge-danger text-light">
                    <span class=" ">0</span>
                </div>
            </div>
        </li>
        <?php endif; ?>
        <?php endif; ?>

        <!-- Theme Toggle -->
        <li class="nav-item">
            <a href="#" class="nav-link nav-link-lg theme-toggle" data-toggle="tooltip" title="Toggle Theme">
                <i class="fas fa-moon"></i>
            </a>
        </li>

        <li class="dropdown">
            <a href="<?php echo e(route('admin.profile')); ?>" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                <img alt="image" src="<?php echo e(auth()->user()->images); ?>" class="rounded-circle mr-1">
                <div class="d-sm-none d-lg-inline-block"><?php echo e(__('topbar_menu.hi')); ?>, <?php echo e(auth()->user()->name); ?></div>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <a href="<?php echo e(route('admin.profile')); ?>" class="dropdown-item has-icon">
                    <i class="far fa-user"></i> <?php echo e(__('topbar_menu.profile')); ?>

                </a>
                <div class="dropdown-divider"></div>
                <a href="<?php echo e(route('logout')); ?>" onclick="event.preventDefault();document.getElementById('logout-form').submit();" class="dropdown-item has-icon text-danger">
                    <i class="fas fa-sign-out-alt"></i> <?php echo e(__('topbar_menu.logout')); ?>

                </a>
                <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" class="display-none">
                    <?php echo csrf_field(); ?>
                </form>
            </div>
        </li>
    </ul>
</nav><?php /**PATH /var/www/html/resources/views/admin/layouts/components/navigation.blade.php ENDPATH**/ ?>