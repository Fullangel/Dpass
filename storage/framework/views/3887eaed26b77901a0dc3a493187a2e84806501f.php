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
<script src="https://www.gstatic.com/firebasejs/8.3.2/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.3.2/firebase-messaging.js"></script>


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
         
        // web_token
        var firebaseConfig = {
            apiKey: "<?php echo e(setting('apiKey')); ?>",
            authDomain: "<?php echo e(setting('authDomain')); ?>",
            projectId: "<?php echo e(setting('projectId')); ?>",
            storageBucket: "<?php echo e(setting('storageBucket')); ?>",
            messagingSenderId: "<?php echo e(setting('messagingSenderId')); ?>",
            appId: "<?php echo e(setting('appId')); ?>",
            measurementId: "<?php echo e(setting('measurementId')); ?>"
        };
        firebase.initializeApp(firebaseConfig);
        const messaging = firebase.messaging();

        startFCM();
            $.ajax({
                    url: "https://entrada.pro/public/api/v1/me"
            }).then(function(data) {
                console.log(data)
             });
        function startFCM() {
            messaging.requestPermission()
                .then(function() {
                    return messaging.getToken()
                })
                .then(function(response) {
                    $.ajax({
                        url: '<?php echo e(route("admin.store.token")); ?>',
                        type: 'POST',
                        data: {
                            token: response
                        },
                        dataType: 'JSON',
                        success: function(response) {

                        },
                        error: function(error) {

                        },
                    });
                }).catch(function(error) {

                });
        }
        messaging.onMessage(function(payload) {
            const title = payload.notification.title;
            const options = {
                body: payload.notification.body,
                icon: payload.notification.icon,
            };

            sound();
            window.location.reload();
            new Notification(title, options);
        });

        <?php if(session('success')): ?>
        iziToast.success({
            title: 'Success',
            message: '<?php echo e(session('
            success ')); ?>',
            position: 'topRight'
        });
        <?php endif; ?>

        <?php if(session('error')): ?>
        iziToast.error({
            title: 'Error',
            message: '<?php echo e(session('
            error ')); ?>',
            position: 'topRight'
        });
        <?php endif; ?>
    });
</script>
<?php /**PATH /var/www/html/resources/views/admin/layouts/components/script.blade.php ENDPATH**/ ?>