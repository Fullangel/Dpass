<!DOCTYPE>
<html>

<?php echo $__env->make('frontend.layouts.partials.head._head', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<body class="pm-home pm-home-css-custom">
    

    <header id="pm-header" class="pm-main-header  header-type-one">
        <div class="container">
            <div class="pm-main-header-content clearfix">
                <div class="pm-logo float-left">
                    <?php if(setting('site_logo')): ?>
                    <a href="<?php echo e(route('/')); ?>">
                        <img src="<?php echo e(asset('images/'.setting('site_logo'))); ?>" data-inject-svg="" alt="" style="height: 130px !important;">
                    </a>
                    <?php endif; ?>
                </div>

                <div class="pm-main-menu-item float-right">
                    <div class="pm-header-btn text-center text-capitalize float-right">
                        <?php if(auth()->user()): ?>
                        <a href="<?php echo e(route('admin.dashboard.index')); ?>"><?php echo e(__('frontend.go_to_dashboard')); ?></a>
                        <?php else: ?>
                        <a href="<?php echo e(route('login')); ?>"><?php echo e(__('frontend.login')); ?></a>
                        <?php endif; ?>
                    </div>

                    <!-- Theme Toggle -->
                    <div class="pm-header-btn theme-toggle-container">
                        <a href="#" class="theme-toggle" title="Toggle Theme">
                            <i class="fas fa-moon"></i>
                        </a>
                    </div>
                </div>
            </div>
            <!-- /desktop menu -->
            <div class="pm-mobile_menu relative-position">
                <div class="pm-mobile_menu_button pm-open_mobile_menu">
                    <i class="fas fa-bars"></i>
                </div>
                <div class="pm-mobile_menu_wrap">
                    <div class="mobile_menu_overlay pm-open_mobile_menu"></div>
                    <div class="pm-mobile_menu_content">
                        <div class="pm-mobile_menu_close pm-open_mobile_menu">
                            <i class="far fa-times-circle"></i>
                        </div>
                        <div class="m-brand-logo text-center">
                            <a href="<?php echo e(route('/')); ?>"><img src="<?php echo e(asset('images/'.setting('site_logo'))); ?>" alt="logo"></a>
                        </div>
                        <nav class="pm-mobile-main-navigation  clearfix ul-li">
                            <ul id="m-main-nav" class="navbar-nav text-capitalize clearfix">
                                <?php if(auth()->user()): ?>
                                <li><a href="<?php echo e(route('admin.dashboard.index')); ?>"><?php echo e(__('frontend.go_to_dashboard')); ?></a></li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                </div>
                <!-- /Mobile-Menu -->
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="main" data-mobile-height="">
        <?php echo $__env->yieldContent('content'); ?>
    </div>
    <!-- Main Content -->

    <?php echo $__env->yieldContent('extras'); ?>

    <?php echo $__env->yieldPushContent('modals'); ?>

    <?php echo $__env->make('frontend.layouts.partials.script._scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <?php echo $__env->yieldPushContent('js'); ?>

</body>

</html>
<?php /**PATH /var/www/html/resources/views/frontend/layouts/frontend.blade.php ENDPATH**/ ?>