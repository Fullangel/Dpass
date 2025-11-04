<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">

<?php echo $__env->make('admin.layouts.components.head', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<body>
    <audio id="myAudio1">
        <source src="<?php echo e(asset('beep.mp3')); ?>" type="audio/mpeg">
    </audio>
    <div id="app">
        <div class="main-wrapper">
            <?php echo $__env->make('admin.layouts.components.navigation', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php echo $__env->make('admin.layouts.components.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

            <!-- Main Content -->
            <div class="main-content">
                <?php echo $__env->yieldContent('main-content'); ?>
            </div>
            <?php echo $__env->make('admin.layouts.components.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>
    </div>
    <?php echo $__env->make('admin.layouts.components.script', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->yieldPushContent('js'); ?>
</body>

</html>
<?php /**PATH /var/www/html/resources/views/admin/layouts/master.blade.php ENDPATH**/ ?>