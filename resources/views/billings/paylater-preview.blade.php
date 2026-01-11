@extends('layouts.print-invoice')

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
                <div class="invoice-title">Konfirmasi Pay Later</div>
                <div>No. Billing : <strong>{{ $billing->billing_number }}</strong></div>
                <div>Tanggal Cetak : {{ now()->translatedFormat('d F Y') }}</div>
            </td>
        </tr>
    </table>

    <hr>

    {{-- CUSTOMER --}}
    <table class="info-table">
        <tr>
            <td>
                <strong>Pelanggan</strong><br>
                {{ $billing->customer->name }} ({{ $billing->customer->customer_id }})<br>
                {{ $billing->customer->address }}<br>
                {{ $billing->customer->contact }}
            </td>
            <td>
                <strong>Petugas</strong><br>
                {{ $billing->customer->user->name }}<br>
                {{ $billing->customer->user->email }}<br>
                {{ $billing->customer->user->contact }}
            </td>
        </tr>
    </table>

    {{-- PAYLATER INFO --}}
    <div class="period" style="margin-top:15px;">
        <strong>Periode Tagihan:</strong>
        {{ $billing->periode->display_name ?? $billing->billingSubscription->billingPeriode->name }}
    </div>

    <div class="period">
        <strong>Status Pembayaran:</strong>
        <span style="color:#d35400;font-weight:bold;">PAY LATER</span>
    </div>

    <div class="period">
        <strong>Batas Pembayaran Pay Later:</strong>
        <span style="color:#c0392b;font-weight:bold;">
            {{ \Carbon\Carbon::parse($billing->paylater_until)->translatedFormat('d F Y') }}
        </span>
    </div>

    {{-- ITEMS --}}
    <table class="items-table" style="margin-top:15px;">
        <thead>
            <tr>
                <th style="width:45%">Item</th>
                <th style="width:10%">Qty</th>
                <th style="width:20%">Harga</th>
                <th style="width:25%">Subtotal</th>
            </tr>
        </thead>
        <tbody>

            @foreach ($packages as $item)
                <tr>
                    <td>{{ $item->item_name }} <span class="muted">(Package)</span></td>
                    <td class="center">{{ $item->qty }}</td>
                    <td class="right">@moneyIDR($item->price)</td>
                    <td class="right">@moneyIDR($item->subtotal)</td>
                </tr>
            @endforeach

            @foreach ($addons as $item)
                <tr>
                    <td>{{ $item->item_name }} <span class="muted">(Addon)</span></td>
                    <td class="center">{{ $item->qty }}</td>
                    <td class="right">@moneyIDR($item->price)</td>
                    <td class="right">@moneyIDR($item->subtotal)</td>
                </tr>
            @endforeach

            @foreach ($discounts as $item)
                <tr class="discount">
                    <td>{{ $item->item_name }} <span class="muted">(Discount)</span></td>
                    <td class="center">{{ $item->qty }}</td>
                    <td class="right">@moneyIDR(abs($item->price))</td>
                    <td class="right">@moneyIDR(abs($item->subtotal))</td>
                </tr>
            @endforeach

        </tbody>
    </table>

    {{-- SUMMARY --}}
    <table class="summary-table">
        <tr>
            <td>Subtotal</td>
            <td class="right">@moneyIDR($billing->subtotal)</td>
        </tr>
        <tr class="discount">
            <td>Diskon</td>
            <td class="right">@moneyIDR(abs($billing->discount_amount))</td>
        </tr>
        <tr class="total">
            <td>Total Tagihan</td>
            <td class="right">@moneyIDR($billing->total_amount)</td>
        </tr>
        <div class="summary-note muted" style="font-size:11px;margin-top:4px;">
            * Total sudah termasuk PPN
        </div>
    </table>

    {{-- PAYLATER NOTE --}}
    <div class="payment-info" style="margin-top:15px;">
        <strong>Ketentuan Pay Later</strong>
        <ul>
            <li>Pembayaran wajib dilakukan sebelum tanggal
                <strong>{{ \Carbon\Carbon::parse($billing->paylater_until)->translatedFormat('d F Y') }}</strong>.
            </li>
            <li>Apabila melewati batas waktu, layanan dapat dibatasi sementara.</li>
            <li>Bukti pembayaran harap dikirimkan melalui kanal resmi.</li>
        </ul>
        <br>
        <strong>{{ __('billing.invoice.payment_info.instruction') }}</strong><br>
        {{ __('billing.invoice.payment_info.transfer') }}
        <ul>
            @foreach ($banks as $bank)
                <li>
                    <strong>{{ $bank->bank }}</strong> —
                    {{ $bank->account_number }} a/n {{ $bank->name }}
                </li>
            @endforeach
        </ul>
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        {{ __('billing.invoice.footer.copyright') }} © {{ now()->year }}.<br>
        Dokumen ini merupakan konfirmasi Pay Later dan bukan bukti pembayaran.
    </div>
@endsection
