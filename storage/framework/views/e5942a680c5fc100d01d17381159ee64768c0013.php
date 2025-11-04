<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title><?php echo e(isset($sitetitle) ? ucfirst($sitetitle) : "Bienvenidos Al SENIAT"); ?></title>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>" />
    <!-- fav icon -->
    <link rel="icon" type="image/x-icon" href="<?php echo e(asset('images/site_logo2.png')); ?>">

    <!-- General CSS Files -->
    <link rel="stylesheet" href="<?php echo e(asset('assets/modules/bootstrap/dist/css/bootstrap.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/modules/@fortawesome/fontawesome-free/css/all.min.css')); ?>">

    <!-- CSS Libraries -->
    <link rel="stylesheet" href="<?php echo e(asset('assets/modules/izitoast/dist/css/iziToast.min.css')); ?>">
    <?php echo $__env->yieldContent('css'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/dropzone.css')); ?>">

    <!-- Theme CSS -->
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/theme-variables.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/theme-overrides.css')); ?>">

    <!-- Template CSS -->
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/style.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/components.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/datatables-dark-theme.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/custom.css')); ?>">




</head><?php /**PATH /var/www/html/resources/views/admin/layouts/components/head.blade.php ENDPATH**/ ?>