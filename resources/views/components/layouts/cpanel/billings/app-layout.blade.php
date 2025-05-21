<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="Ari Purnawan">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $title ?? 'Customer Management' }}</title>
    <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('images/favicon/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon/favicon-16x16.png') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('images/favicon/favicon.png') }}" />
    <link rel="manifest" href="{{ asset('assets/site.webmanifest') }}">
    <link rel="mask-icon" href="{{ asset('images/favicon/safari-pinned-tab.svg') }}" color="#5bbad5">
    @vite('resources/sass/app.scss')
    <!-- Custom fonts for this template-->
    <link href="{{ asset('css/fontawsome-free-all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/notify/notify.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/sweet-alert/sweet-alert.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/datatables/datatables.min.css') }}" rel="stylesheet">
    <!-- Custom styles for this template-->
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">

    <!-- Custom styles for this Page-->
    @yield('custom_styles')

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <x-layouts.title />
            <x-layouts.cpanel.billings.navigation />
        </ul>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <!-- Topbar -->
                <x-layouts.top-bar />
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.toggleExpand = function(slug) {
                const row = document.getElementById('expand-' + slug);
                if (row) {
                    row.classList.toggle('d-none');
                    row.classList.toggle('d-table-row');
                }
            };
        });
    </script>
    <script>
        $(document).ready(function() {
            // Ketika tombol bayar diklik
            $(document).on('click', '.billing-submit-btn', function() {
                const $row = $(this).closest('tr'); // atau '.billing-row'
                const $select = $row.find('.billing-method-select');
                const paymentMethod = $select.val();

                if (!paymentMethod) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Metode belum dipilih',
                        text: 'Pilih metode pembayaran dulu!',
                    });
                    return;
                }

                Swal.fire({
                    title: 'Konfirmasi Pembayaran?',
                    text: 'Metode pembayaran: ' + paymentMethod.toUpperCase(),
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, bayar',
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#d33',
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    const slug = $(this).data('slug'); // Masih boleh pakai slug statis
                    const token = $('meta[name="csrf-token"]').attr('content');

                    const form = $('<form>', {
                        method: 'POST',
                        action: '/cpanel/billings/' + slug + '/payment/',
                    });

                    form.append($('<input>', {
                        type: 'hidden',
                        name: '_token',
                        value: token
                    }));
                    form.append($('<input>', {
                        type: 'hidden',
                        name: '_method',
                        value: 'PATCH'
                    }));
                    form.append($('<input>', {
                        type: 'hidden',
                        name: 'payment_methode',
                        value: paymentMethod
                    }));

                    $('body').append(form);
                    form.submit();
                });
            });
        });
    </script>
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
