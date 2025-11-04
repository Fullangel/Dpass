<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title><?php echo e(setting('site_name'). ' - ' . __('Login')); ?></title>
    <!-- General CSS Files -->
    <link rel="stylesheet" href="<?php echo e(asset('assets/modules/bootstrap/dist/css/bootstrap.min.css')); ?>">
    <!-- CSS Libraries -->

    <!-- Template CSS -->
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/style.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/components.css')); ?>">
</head>

<body>
    <div id="app">
        <section class="section">
            <div class="container mt-5">
                <div class="row">
                    <div class="col-12 col-sm-8 offset-sm-2 col-md-6 offset-md-3 col-lg-6 offset-lg-3 col-xl-4 offset-xl-4">
                        <div class="login-brand">
                            <?php if(setting('site_logo')): ?>
                                <img src="<?php echo e(asset('images/'.setting('site_logo'))); ?>" alt="logo" width="300">
                            <?php else: ?>
                                <b><?php echo e(setting('site_name')); ?></b>
                            <?php endif; ?>
                        </div>

                        <div class="card card-primary">
                            <div class="card-header">
                                <h4><?php echo e(__('Ingresar')); ?></h4>
                            </div>

                            <div class="card-body">
                                <form method="POST" action="<?php echo e(route('login')); ?>">
                                    <?php echo csrf_field(); ?>
                                    <div class="form-group">
                                        <label for="email"><?php echo e(__('Email')); ?></label><span class="text-danger"> *</span>
                                        <input id="email" type="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="email" value="<?php echo e(old('email')); ?>" />
                                        <?php $__errorArgs = ['email'];
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
                                        <div class="d-block">
                                            <label for="password" class="control-label"><?php echo e(__('Contraseña')); ?></label><span class="text-danger"> *</span>
                                        </div>
                                        <input id="password" type="password" class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="password"/>
                                        <?php $__errorArgs = ['password'];
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
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input" type="checkbox" name="remember" id="remember" <?php echo e(old('remember') ? 'checked' : ''); ?>>

                                            <label class="custom-control-label" for="remember">
                                                <?php echo e(__('Recuerdame')); ?>

                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary btn-lg btn-block" tabindex="4">
                                            <?php echo e(__('Ingresar')); ?>

                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <?php if(env('DEMO')): ?>
                            <div class="card mx-auto text-center mt-4" style="max-width: 500px;">
                                <div class="card-header">
                                    <h4 class="mb-0"><?php echo e(__('For Quick Demo Login Click Below...')); ?></h4>
                                </div>
                                <div class="card-body">
                                    <div class="buttons">
                                        <button id="demo-admin" class="btn btn-primary"><?php echo e(__('Admin')); ?></button>
                                        <button id="demo-reception" class="btn btn-info"><?php echo e(__('Reception')); ?></button>
                                        <button id="demo-employee" class="btn btn-success"><?php echo e(__('Employee')); ?></button>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="simple-footer">
                            <?php echo e(setting('site_footer')); ?>

                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>


    <script src="<?php echo e(asset('frontend/frontend/js/jquery.js')); ?>"></script>
    <script src="<?php echo e(asset('frontend/js/demo-login.js')); ?>"></script>
</body>
</html>
<?php /**PATH /var/www/html/resources/views/admin/auth/login.blade.php ENDPATH**/ ?>