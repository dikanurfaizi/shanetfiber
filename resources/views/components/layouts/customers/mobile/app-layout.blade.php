<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite('resources/sass/app.scss')
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/fontawsome-free-all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{asset('assets/notify/notify.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/sweet-alert/sweet-alert.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/datatables/datatables.min.css')}}" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    <link href="{{ asset('css/mobile.css') }}" rel="stylesheet">
    @yield('custom_styles')
</head>

<body id="page-top">
    <div id="wrapper">
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <x-layouts.customers.mobile.top-bar />
                <!-- Main page content-->
                <div class="container-fluid">
                    <div class="card shadow mb-4">
                        {{ $slot }}
                    </div>
                </div>
            </div>
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <div>
                            <span>
                                Copyright &copy; Shanum Network Fiber 2024
                            </span>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    @vite('resources/js/app.js')

    <!-- Core plugin JavaScript-->
    <script src="{{ asset('assets/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('js/jquery.easing-1.4.1.min.js') }}"></script>
    <script src="{{asset('js/bootstrap.bundle.min.js')}}" crossorigin="anonymous"></script>
    <!-- Custom scripts for all pages-->
    <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>
    <script src="{{asset('assets/notify/notify.min.js')}}"></script>
    <script src="{{asset('assets/sweet-alert/sweet-alert.min.js')}}"></script>
    <script src="{{asset('assets/vendor/datatables/datatables.min.js')}}"></script>
    <!-- Page level custom scripts -->
    @yield('custom_scripts')
    @if ($message = Session::get('success'))

    <script>
        Swal.fire({
            icon: 'success',
            text: '{{ $message }}',
        })
    </script>
    @endif

    @if ($message = Session::get('error'))

    <script>
        Swal.fire({
            icon: 'error',
            text: '{{ $message }}',
        })
    </script>
    @endif
</body>

</html>