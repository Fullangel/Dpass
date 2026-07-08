<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

@include('admin.layouts.components.head')
<script>
    (function () {
        document.documentElement.setAttribute('data-theme', 'light');
        try {
            localStorage.setItem('preferred-theme', 'light');
        } catch (e) {}
    })();
</script>

<body>
    <div id="app">
        <div class="main-wrapper">
            <div class="main-content">
                @yield('main-content')
            </div>
        </div>
    </div>
    @include('admin.layouts.components.script')
    @stack('js')
</body>

</html>
