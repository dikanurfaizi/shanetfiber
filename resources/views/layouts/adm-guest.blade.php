<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="Dian Ardiansyah">
    <meta name="google-site-verification" content="VM3PKBo1GgIleHGysVVsGcowiPLWELh1d5qJ7cIP_pI" />
    <meta name="msvalidate.01" content="54E283E0B2DF8EDBB2221E556F92DECC" />
    <title>{{ config('app.name', 'Shanum Network Fiber') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="48x48" href="{{ asset('images/favicon/favicon-48x48.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/favicon/favicon-48x48.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('images/favicon/favicon-48x48.png') }}">

    @vite('resources/sass/app.scss')
    <!-- Custom fonts for this template-->
    <link href="{{ asset('css/fontawsome-free-all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/sweet-alert/sweet-alert.min.css') }}" rel="stylesheet">
    <!-- Custom styles for this template-->
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/swiper/swiper-bundle.min.css') }}" rel="stylesheet">

    {{-- YIELD UNTUK CUSTOM STYLES DARI PAGE LOGIN --}}
    @yield('custom_styles')

</head>

<body>

    {{-- @yield('content') dipanggil langsung tanpa pembungkus .container tambahan --}}
    @yield('content')

    @vite('resources/js/app.js')

    <!-- Core plugin JavaScript-->
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/jquery.easing-1.4.1.min.js') }}"></script>
    <!-- Custom scripts for all pages-->
    <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/swiper/swiper-bundle.min.js') }}"></script>

    {{-- Memanggil skrip kustom dari halaman anak --}}
    @yield('custom_scripts')

    @if ($message = Session::get('error'))
        <script>
            Swal.fire({
                icon: 'error',
                text: '{{ $message }}',
            })
        </script>
    @endif

    @if ($message = Session::get('success'))
        <script>
            Swal.fire({
                icon: 'success',
                text: '{{ $message }}',
            })
        </script>
    @endif
</body>

</html>
