@extends('layouts.print')

@section('content')
    {{-- WATERMARK --}}
    <div class="invoice-watermark"></div>

    {{-- HEADER --}}
    <table class="header-table">
        <tr>
            <td class="logo">
                <img src="{{ public_path('images/receipt_logo.png') }}" style="max-width:140px">
            </td>
            <td class="header-info">
                <div class="invoice-title">RECEIPT</div>
                <div>Receipt No : <strong>{{ $receipt->number }}</strong></div>
                <div>Paid At :
                    {{ \Carbon\Carbon::parse($receipt->payment_time)->translatedFormat('d M Y H:i') }}
                </div>
            </td>
        </tr>
    </table>

    <hr>

    {{-- PAID BY / RECEIVED BY --}}
    <table class="info-table">
        <tr>
            <td>
                <strong>Paid By</strong><br>
                {{ $receipt->customer_name }}
                ({{ $receipt->customer->customer_id }})<br>
                {{ $receipt->customer_address }}<br>
                {{ $receipt->customer->contact ?? '-' }}
            </td>
            <td>
                <strong>Received By</strong><br>
                Shanum Network Fiber<br>
                {{ $receipt->teller_name ?? '-' }}<br>
                {{ $websystem->email }}<br>
                {{ $websystem->phone }}
            </td>
        </tr>
    </table>

    {{-- ITEMS --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:45%">Item</th>
                <th style="width:10%">Qty</th>
                <th style="width:20%">Price</th>
                <th style="width:25%">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($receiptItems as $item)
                <tr @if ($item->type === 'discount') class="discount" @endif>
                    <td>
                        {{ $item->name }}
                        <span class="muted">({{ ucfirst($item->type) }})</span>
                    </td>
                    <td class="center">{{ $item->qty }}</td>
                    <td class="right">@moneyIDR(abs($item->unit_price))</td>
                    <td class="right">@moneyIDR(abs($item->subtotal))</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- SUMMARY --}}
    <table class="summary-table">
        <tr>
            <td>Sub Total</td>
            <td class="right">@moneyIDR($subtotal)</td>
        </tr>
        <tr class="discount">
            <td>Discount</td>
            <td class="right">@moneyIDR(abs($discount))</td>
        </tr>
        {{-- <tr>
            <td>PPN</td>
            <td class="right">@moneyIDR($receipt->tax)</td>
        </tr> --}}
        <tr class="total">
            <td>Total Paid</td>
            <td class="right">@moneyIDR($total)</td>
        </tr>
        <div class="summary-note muted" style="font-size:11px;margin-top:4px;">
            * Total sudah termasuk PPN
        </div>
    </table>

    {{-- PAYMENT DETAILS --}}
    <div class="payment-info">
        <strong>Payment Details</strong><br>
        <table class="payment-details">
            <tr>
                <td>Payment Method</td>
                <td>{{ ucfirst($receipt->payment_methode) }}</td>
            </tr>

            @if ($receipt->payment_methode === 'transfer' || $receipt->payment_methode === 'online')
                <tr>
                    <td>Bank</td>
                    <td>{{ $receipt->bank_name }}</td>
                </tr>
                <tr>
                    <td>Account Name</td>
                    <td>{{ $receipt->account_name }}</td>
                </tr>
                <tr>
                    <td>Account Number</td>
                    <td>{{ $receipt->account_number }}</td>
                </tr>
            @endif

            @if ($receipt->billing_number)
                <tr>
                    <td>Reference ID</td>
                    <td>{{ $receipt->billing_number }}</td>
                </tr>
            @endif
        </table>
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        Tanda terima dibuat oleh Shanum Network Fiber © {{ now()->year }}.<br>
        Struk ini dibuat oleh sistem dan tidak memerlukan tanda tangan.
    </div>
@endsection
