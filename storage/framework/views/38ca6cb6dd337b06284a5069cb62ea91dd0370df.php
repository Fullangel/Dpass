<?php echo $__env->yieldPushContent('scripts'); ?>
<!-- Scripts -->
<!-- JS library -->
<script src="<?php echo e(asset('frontend/frontend/js/jquery.js')); ?>"></script>
<script src="<?php echo e(asset('frontend/frontend/js/appear.js')); ?>"></script>
<script src="<?php echo e(asset('assets/modules/popper.js/dist/popper.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/modules/bootstrap/dist/js/bootstrap.min.js')); ?>"></script>
<script src="<?php echo e(asset('frontend/frontend/js/aos.js')); ?>"></script>
<script src="<?php echo e(asset('frontend/frontend/js/owl.js')); ?>"></script>
<script src="<?php echo e(asset('frontend/frontend/js/wow.min.js')); ?>"></script>
<script src="<?php echo e(asset('frontend/frontend/js/pagenav.js')); ?>"></script>
<script src="<?php echo e(asset('frontend/frontend/js/parallax-scroll.js')); ?>"></script>
<script src="<?php echo e(asset('frontend/frontend/js/jquery.paroller.min.js')); ?>"></script>
<script src="<?php echo e(asset('frontend/frontend/js/jquery.barfiller.js')); ?>"></script>
<script src="<?php echo e(asset('frontend/frontend/js/slick.js')); ?>"></script>
<script src="<?php echo e(asset('frontend/frontend/js/jquery.mCustomScrollbar.concat.min.js')); ?>"></script>
<script src="<?php echo e(asset('frontend/frontend/js/script.js')); ?>"></script>
<script src="<?php echo e(asset('assets/modules/select2/dist/js/select2.full.min.js')); ?>"></script>

<!-- Theme JS -->
<script src="<?php echo e(asset('assets/js/theme.js')); ?>"></script>


<script src="<?php echo e(asset('vendor/offline-libs/jquery.mousewheel.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/modules/moment/min/moment.min.js')); ?>"></script>
<script src="<?php echo e(asset('vendor/offline-libs/daterangepicker.min.js')); ?>"></script>
<script src="<?php echo e(asset('vendor/offline-libs/tinymce.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/modules/summernote/summernote-bs4.js')); ?>"></script>
<script src="<?php echo e(asset('assets/modules/izitoast/dist/js/iziToast.min.js')); ?>"></script>

<!-- Scripts -->
<?php echo $__env->yieldContent('scripts'); ?>
<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    <?php if(session('success')): ?>
    iziToast.success({
        title: 'Success',
        message: '<?php echo e(session('success')); ?>',
        position: 'topRight'
    });
    <?php endif; ?>

    <?php if(session('error')): ?>
    iziToast.error({
        title: 'Error',
        message: '<?php echo e(session('error')); ?>',
        position: 'topRight'
    });
    <?php endif; ?>
</script>

<?php /**PATH /var/www/html/resources/views/frontend/layouts/partials/script/_scripts.blade.php ENDPATH**/ ?>