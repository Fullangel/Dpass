<!-- General JS Scripts -->
<script src="{{ asset('assets/modules/jquery/dist/jquery.min.js') }}"></script>
<script src="{{ asset('assets/modules/popper.js/dist/popper.min.js') }}"></script>
<script src="{{ asset('assets/modules/bootstrap/dist/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/modules/jquery.nicescroll/dist/jquery.nicescroll.min.js') }}"></script>
<script src="{{ asset('assets/modules/moment/min/moment.min.js') }}"></script>
<script src="{{ asset('assets/js/dropzone.min.js') }}"></script>
<script src="{{ asset('assets/js/stisla.js') }}"></script>

<!-- JS Libraies -->
<script src="{{ asset('assets/modules/izitoast/dist/js/iziToast.min.js') }}"></script>
@yield('scripts')

<!-- Theme JS -->
<script src="{{ asset('assets/js/theme.js') }}"></script>

<!-- Template JS File -->
<script src="{{ asset('assets/js/scripts.js') }}"></script>
<script src="{{ asset('js/custom.js') }}"></script>
@php
    $fcmProjectId = trim((string) setting('projectId'));
    $fcmEnabled = $fcmProjectId !== '';
@endphp
@if ($fcmEnabled)
<script src="{{ asset('vendor/offline-libs/firebase-app.js') }}"></script>
<script src="{{ asset('vendor/offline-libs/firebase-messaging.js') }}"></script>
@endif

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

        @if ($fcmEnabled)
        (function () {
            var firebaseConfig = {
                apiKey: @json(setting('apiKey')),
                authDomain: @json(setting('authDomain')),
                projectId: @json($fcmProjectId),
                storageBucket: @json(setting('storageBucket')),
                messagingSenderId: @json(setting('messagingSenderId')),
                appId: @json(setting('appId')),
                measurementId: @json(setting('measurementId'))
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
                            url: '{{ route("admin.store.token") }}',
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
        @endif

        @if(session('success'))
        iziToast.success({
            title: 'Success',
            message: @json(session('success')),
            position: 'topRight'
        });
        @endif

        @if(session('error'))
        iziToast.error({
            title: 'Error',
            message: @json(session('error')),
            position: 'topRight'
        });
        @endif
    });
</script>
