@extends('layouts.thermal')

@section('content')
    <div class="watermark">
        {{ strtoupper($websystem->title) }}
    </div>

    <div class="sheet">

        {{-- HEADER --}}
        <div class="center logo">
            <img src="{{ public_path('images/receipt_logo.png') }}">
        </div>

        <div class="center title">
            PAYMENT RECEIPT<br />
            {{ $receipt->number }}
        </div>

        {{-- CUSTOMER --}}
        <div>
            Customer : {{ $receipt->customer_name }}<br>
            ID : {{ $receipt->customer->customer_id }}<br>
            Period : {{ $receipt->periode }}
        </div>

        <div class="divider"></div>

        {{-- ITEMS --}}
        <table>
            @foreach ($receiptItems as $item)
                {{-- Nama item --}}
                <tr>
                    <td colspan="3" class="left">
                        {{ $item->name }}
                    </td>
                </tr>

                {{-- Qty x Price = Subtotal --}}
                <tr>
                    <td class="col-item left">
                        {{ $item->qty }} x {{ number_format($item->unit_price, 0, ',', '.') }}
                    </td>
                    <td class="col-rp left">Rp</td>
                    <td class="col-val right">
                        {{ number_format($item->subtotal, 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </table>

        <div class="divider"></div>

        {{-- SUMMARY --}}
        <table class="summary">
            <tr>
                <td>Subtotal</td>
                <td class="right">Rp</td>
                <td class="right">{{ number_format($subtotal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>PPN</td>
                <td class="right">Rp</td>
                <td class="right">{{ number_format($receipt->tax, 0, ',', '.') }}</td>
            </tr>
            <tr class="total">
                <td>TOTAL PAID</td>
                <td class="right">Rp</td>
                <td class="right">{{ number_format($total, 0, ',', '.') }}</td>
            </tr>
        </table>

        <div class="divider"></div>

        {{-- PAYMENT --}}
        <div>
            Paid via : {{ ucfirst($receipt->payment_methode) }}<br>
            Paid at :
            {{ \Carbon\Carbon::parse($receipt->payment_time)->translatedFormat('d M Y H:i') }}<br>
        </div>

        @if (in_array($receipt->payment_methode, ['transfer', 'online']))
            <div>
                Bank : {{ $receipt->bank_name }}<br>
                Acc : {{ $receipt->account_number }}<br>
                A/N : {{ $receipt->account_name }}
            </div>
        @endif

        {{-- FOOTER --}}
        <div class="footer">
            Valid proof of payment<br>
            {{ strtoupper($websystem->title) }}<br>
            No signature required
        </div>

    </div>
@endsection
