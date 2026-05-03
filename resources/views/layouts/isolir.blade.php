<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="Mehrdad Amini">
    <meta name="google-site-verification" content="VM3PKBo1GgIleHGysVVsGcowiPLWELh1d5qJ7cIP_pI" />
    <meta name="msvalidate.01" content="54E283E0B2DF8EDBB2221E556F92DECC" />
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Pastikan tidak ada / setelah .png -->
    <link rel="icon" type="image/png" sizes="48x48" href="{{ asset('images/favicon/favicon-48x48.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/favicon/favicon-48x48.png') }}" type="image/png">

    <!-- Opsional: Ikon untuk shortcut di HP/Apple -->
    <link rel="apple-touch-icon" href="{{ asset('images/favicon/favicon-48x48.png') }}">

    @vite('resources/sass/app.scss')
    <link href="{{ asset('css/fontawsome-free-all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">

</head>

<body class="bg-gradient-primary">

    <div class="container">

        @yield('content')

    </div>
    @vite('resources/js/app.js')

    <!-- Core plugin JavaScript-->
    <!--<script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/jquery.easing-1.4.1.min.js') }}"></script>
    <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>-->

</body>

</html>
