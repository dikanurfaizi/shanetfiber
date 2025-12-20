<head>
    <title>Invoice {{ $billing->billing_number }}</title>
    <meta charset="utf-8" />
    {{-- STYLE --}}
    <style>
        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #333;
        }

        hr {
            border: none;
            border-top: 1px solid #ddd;
            margin: 10px 0;
        }

        .header-table {
            width: 100%;
        }

        .logo {
            width: 50%;
        }

        .header-info {
            width: 50%;
            text-align: right;
        }

        .invoice-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .info-table {
            width: 100%;
            margin-top: 10px;
        }

        .period {
            margin: 10px 0;
            font-size: 11px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .items-table th {
            background: #f2f2f2;
            border-bottom: 1px solid #ccc;
            padding: 6px;
            font-size: 11px;
        }

        .items-table td {
            border-bottom: 1px solid #eee;
            padding: 6px;
            font-size: 11px;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .muted {
            color: #888;
            font-size: 10px;
        }

        .discount td {
            color: #c0392b;
            font-weight: normal;
        }

        .summary-table {
            width: 40%;
            float: right;
            margin-top: 10px;
        }

        .summary-table td {
            padding: 5px;
            font-size: 11px;
        }

        .summary-table .total td {
            font-weight: bold;
            border-top: 2px solid #333;
        }

        .payment-info {
            clear: both;
            margin-top: 20px;
            font-size: 11px;
        }

        .payment-info ul {
            padding-left: 15px;
            margin: 5px 0;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }

        .invoice-watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            width: 420px;
            height: 420px;
            transform: translate(-50%, -50%) rotate(-15deg);
            background-image: url('{{ public_path('images/receipt_logo.png') }}');
            background-repeat: no-repeat;
            background-size: contain;
            opacity: 0.1;
            z-index: -1;
        }
    </style>
</head>

<body id="page-top">



    @yield('content')


    @vite('resources/js/app.js')


</body>

</html>
