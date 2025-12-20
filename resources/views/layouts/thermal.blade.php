<head>
    <meta charset="UTF-8">
    <title>Receipt {{ $receipt->number }}</title>

    <style>
        @page {
            size: 58mm auto;
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: monospace;
            background: #fff;
        }

        .sheet {
            width: 46mm;
            /* AMAN untuk printer 58mm */
            margin: 0 auto;
            padding: 2mm;
            font-size: 9px;
            line-height: 1.35;
            position: relative;
        }

        /* WATERMARK */
        .watermark {
            position: fixed;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 16px;
            font-weight: bold;
            color: rgba(0, 0, 0, 0.06);
            white-space: nowrap;
            z-index: 0;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .left {
            text-align: left;
        }

        .logo img {
            width: 28mm;
            /* DIPERKECIL */
            margin-bottom: 3px;
        }

        .title {
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 4px;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 4px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            vertical-align: top;
            padding: 1px 0;
            word-break: break-word;
        }

        /* ITEM TABLE */
        .col-item {
            width: 60%;
        }

        .col-rp {
            width: 10%;
        }

        .col-val {
            width: 30%;
        }

        .summary td {
            padding: 1px 0;
        }

        .total {
            font-weight: bold;
            font-size: 10px;
        }

        .footer {
            margin-top: 6px;
            padding-top: 4px;
            border-top: 1px dashed #000;
            text-align: center;
            font-size: 8px;
        }
    </style>
</head>

<body onload="window.print()">
    @yield('content')
</body>

</html>
