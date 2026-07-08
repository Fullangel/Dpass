<!-- General JS Scripts -->
<script src="<?php echo e(asset('assets/modules/jquery/dist/jquery.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/modules/popper.js/dist/popper.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/modules/bootstrap/dist/js/bootstrap.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/modules/jquery.nicescroll/dist/jquery.nicescroll.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/modules/moment/min/moment.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/js/dropzone.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/js/stisla.js')); ?>"></script>

<!-- JS Libraies -->
<script src="<?php echo e(asset('assets/modules/izitoast/dist/js/iziToast.min.js')); ?>"></script>
<?php echo $__env->yieldContent('scripts'); ?>

<!-- Theme JS -->
<script src="<?php echo e(asset('assets/js/theme.js')); ?>"></script>

<!-- Template JS File -->
<script src="<?php echo e(asset('assets/js/scripts.js')); ?>"></script>
<script src="<?php echo e(asset('js/custom.js')); ?>"></script>
<?php
    $fcmProjectId = trim((string) setting('projectId'));
    $fcmEnabled = $fcmProjectId !== '';
?>
<?php if($fcmEnabled): ?>
<script src="<?php echo e(asset('vendor/offline-libs/firebase-app.js')); ?>"></script>
<script src="<?php echo e(asset('vendor/offline-libs/firebase-messaging.js')); ?>"></script>
<?php endif; ?>

<script type="text/javascript">
    var beep = document.getElementById("myAudio1");

    function sound() {
        beep.play();
    }
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        <?php if($fcmEnabled): ?>
        (function () {
            var firebaseConfig = {
                apiKey: <?php echo json_encode(setting('apiKey'), 15, 512) ?>,
                authDomain: <?php echo json_encode(setting('authDomain'), 15, 512) ?>,
                projectId: <?php echo json_encode($fcmProjectId, 15, 512) ?>,
                storageBucket: <?php echo json_encode(setting('storageBucket'), 15, 512) ?>,
                messagingSenderId: <?php echo json_encode(setting('messagingSenderId'), 15, 512) ?>,
                appId: <?php echo json_encode(setting('appId'), 15, 512) ?>,
                measurementId: <?php echo json_encode(setting('measurementId'), 15, 512) ?>
            };
            firebase.initializeApp(firebaseConfig);
            var messaging = firebase.messaging();

            function startFCM() {
                messaging.requestPermission()
                    .then(function () {
                        return messaging.getToken();
                    })
                    .then(function (token) {
                        if (!token) {
                            return;
                        }
                        $.ajax({
                            url: '<?php echo e(route("admin.store.token")); ?>',
                            type: 'POST',
                            data: { token: token },
                            dataType: 'JSON'
                        });
                    })
                    .catch(function () {});
            }

            startFCM();

            messaging.onMessage(function (payload) {
                var title = (payload.notification && payload.notification.title) ? payload.notification.title : '';
                var options = {
                    body: payload.notification ? payload.notification.body : '',
                    icon: payload.notification ? payload.notification.icon : ''
                };
                sound();
                window.location.reload();
                if (title && window.Notification && Notification.permission === 'granted') {
                    new Notification(title, options);
                }
            });
        })();
        <?php endif; ?>

        <?php if(session('success')): ?>
        iziToast.success({
            title: 'Success',
            message: <?php echo json_encode(session('success'), 15, 512) ?>,
            position: 'topRight'
        });
        <?php endif; ?>

        <?php if(session('error')): ?>
        iziToast.error({
            title: 'Error',
            message: <?php echo json_encode(session('error'), 15, 512) ?>,
            position: 'topRight'
        });
        <?php endif; ?>
    });
</script>
<?php /**PATH /var/www/html/resources/views/admin/layouts/components/script.blade.php ENDPATH**/ ?>