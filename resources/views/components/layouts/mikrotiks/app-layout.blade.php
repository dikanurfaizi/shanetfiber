<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="Mehrdad Amini">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $title ?? 'Customer Management' }}</title>

    @vite('resources/sass/app.scss')
    <!-- Custom fonts for this template-->
    <link href="{{ asset('css/fontawsome-free-all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/notify/notify.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/sweet-alert/sweet-alert.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/datatables/datatables.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">

    <!-- Custom styles for this Page-->
    @yield('custom_styles')

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <x-layouts.title />
            <x-layouts.mikrotiks.navigation />
        </ul>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <!-- Topbar -->
                <x-layouts.top-bar />
                <!-- End of Topbar -->
                <!-- Main page content-->
                <div class="container-fluid">
                    <div class="card shadow mb-4">
                        {{ $slot }}
                    </div>
                </div>
            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <x-layouts.footer />
            <!-- End of Footer -->
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <x-layouts.logout-modal />
    @vite('resources/js/app.js')

    <!-- Core plugin JavaScript-->
    <script src="{{ asset('assets/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('js/jquery.easing-1.4.1.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}" crossorigin="anonymous"></script>
    <!-- Custom scripts for all pages-->
    <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>
    <script src="{{ asset('assets/notify/notify.min.js') }}"></script>
    <script src="{{ asset('assets/sweet-alert/sweet-alert.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables/datatables.min.js') }}"></script>
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

    <button id="floatingSidebarToggle" class="btn btn-toggle-sidebar rounded-circle shadow d-md-none position-fixed"
        style="top: calc(50% - 28px); right: 20px; width: 56px; height: 56px; z-index: 1050;">
        <i id="toggleIcon" class="fas fa-bars"></i>
    </button>

</body>

</html>
