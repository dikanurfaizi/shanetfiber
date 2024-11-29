<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="Mehrdad Amini">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $title ?? '' }} | {{ __('websystem.title') }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/favicon/favicon.png') }}" />
    @vite('resources/sass/app.scss')
    <!-- Custom fonts for this template-->
    <link href="{{ asset('css/fontawsome-free-all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/notify/notify.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/sweet-alert/sweet-alert.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/datatables/datatables.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    <link href="{{ asset('css/mobile.css') }}" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
    <!-- Custom styles for this Page-->
    @yield('custom_styles')

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
        <x-layouts.customers.navigation />
        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <!-- Topbar -->
                <x-layouts.customers.top-bar />
                <!-- End of Topbar -->
                @if ($errors->any())
                    <div class="container-fluid">
                        <div class="card shadow mb-4">

                            <div class="card-body">
                                <div class="form-group mt-4 mb-4">
                                    <div class="pull-right alert alert-danger">
                                        <strong>Whoops!</strong> There were some problems.<br><br>
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                @endif
                <!-- Main page content-->
                <div class="container-fluid">
                    <div class="card shadow mb-4">

                        {{ $slot }}
                    </div>
                </div>
            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <x-layouts.customers.footer />
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
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <form method="POST" action="{{ route('customerLogout') }}">
                        @csrf
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                        <a class="btn btn-primary" onclick="event.preventDefault(); this.closest('form').submit();">
                            <i class="mr-2 fas fa-sign-out-alt"></i>
                            {{ __('Log Out') }}
                        </a>
                    </form>

                </div>
            </div>
        </div>
    </div>
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

</body>

</html>
