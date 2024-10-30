<head>
    <title>Receipt {{ $receipt->number }} - ShaNet</title>
    <meta charset="utf-8" />
    <style>
        @page {
            size: 53mm 100mm;
            margin: 0;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }

        /* Aturan khusus untuk cetak */
        @media print {
            body {
                width: 53mm;
                height: 100mm;
                margin: 0;
                background-color: #ffffff;
            }

            .container {
                width: 50mm;
                padding: 5px;
                margin: 0 auto;
                box-shadow: none;
                overflow: visible;
            }
        }

        .container {
            background-color: #fff;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            width: 40mm;
            max-height: 100mm;
            overflow: auto;
            margin: auto;
        }

        .header {
            text-align: center;
            margin-bottom: 5px;
        }

        .header img {
            width: 35mm;
            height: auto;
        }

        .header .separator {
            border-bottom: 1px solid #ddd;
            margin-top: 3px;
            margin-bottom: 5px;
        }

        .receipt-info {
            text-align: center;
            font-size: 7px;
            color: rgba(6, 6, 6, 0.95);
            margin-bottom: 5px;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }

        .content {
            margin-bottom: 5px;
        }

        .content .section .label,
        .content .section .value {
            font-size: 7px;
            text-align: left;
        }

        .section-margin {
            margin-bottom: 5px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }

        .table th,
        .table td {
            border-top: 1px solid #ddd;
            padding: 2px;
            text-align: left;
            font-size: 7px;
        }

        .table th {
            background-color: #f2f2f2;
            border-bottom: 1px solid #000;
        }

        .table .right {
            text-align: right;
        }

        .table-margin-top {
            padding-top: 10px
        }

        .footer {
            font-size: 7px;
            color: rgba(6, 6, 6, 0.95);
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 3px;
            margin-top: 5px;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 18px;
            color: rgba(0, 128, 0, 0.3);
            font-weight: bold;
            text-align: center;
            white-space: nowrap;
            pointer-events: none;
            z-index: -1;
            text-decoration: underline dotted;
            text-decoration-thickness: 1.5px;
            text-decoration-color: rgba(0, 128, 0, 0.4);
        }
    </style>
</head>

<body onload="window.print();">
    @yield('content')
    {{-- @vite('resources/js/app.js') --}}
</body>

</html>
