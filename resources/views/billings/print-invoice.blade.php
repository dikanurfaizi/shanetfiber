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
                <div class="invoice-title">{{ __('billing.invoice.header.title') }}</div>
                <div>{{ __('billing.invoice.header.invoice_no') }} : <strong>{{ $billing->billing_number }}</strong></div>
                <div>{{ __('billing.invoice.header.printed') }} : {{ now()->translatedFormat('d M Y') }}</div>
                <div>{{ __('billing.invoice.header.due_date') }} :
                    <strong>{{ \Carbon\Carbon::parse($billing->due_date)->translatedFormat('d M Y') }}</strong>
                </div>
            </td>
        </tr>
    </table>

    <hr>

    {{-- BILL TO --}}
    <table class="info-table">
        <tr>
            <td>
                <strong>{{ __('billing.invoice.info.bill_to') }}</strong><br>
                {{ $billing->customer->name }} ({{ $billing->customer->customer_id }})<br>
                {{ $billing->customer->address }}<br>
                {{ $billing->customer->contact }}
            </td>
            <td>
                <strong>{{ __('billing.invoice.info.issued_by') }}</strong><br>
                {{ __('billing.invoice.info.company_name') }}<br>
                {{ $billing->customer->user->name }}<br>
                {{ $billing->customer->user->email }}<br>
                {{ $billing->customer->user->contact }}
            </td>
        </tr>
    </table>

    {{-- PERIOD --}}
    <div class="period">
        <strong>{{ __('billing.invoice.period.billing_period') }}:</strong>
        {{ $billing->billingSubscription->billingPeriode->name }}
    </div>

    {{-- ITEMS TABLE --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:45%">{{ __('billing.invoice.table.item') }}</th>
                <th style="width:10%">{{ __('billing.invoice.table.qty') }}</th>
                <th style="width:20%">{{ __('billing.invoice.table.price') }}</th>
                <th style="width:25%">{{ __('billing.invoice.table.amount') }}</th>
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
            <td>{{ __('billing.invoice.summary.subtotal') }}</td>
            <td class="right">@moneyIDR($billing->subtotal)</td>
        </tr>
        <tr class="discount">
            <td>{{ __('billing.invoice.summary.discount') }}</td>
            <td class="right">@moneyIDR(abs($billing->discount_amount))</td>
        </tr>
        <tr>
            <td>{{ __('billing.invoice.summary.tax') }}</td>
            <td class="right">@moneyIDR($billing->tax_amount)</td>
        </tr>
        <tr class="total">
            <td>{{ __('billing.invoice.summary.total') }}</td>
            <td class="right">@moneyIDR($billing->total_amount)</td>
        </tr>
    </table>

    {{-- PAYMENT INSTRUCTION --}}
    <div class="payment-info">
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
        {{ __('billing.invoice.footer.notes') }}
    </div>
@endsection
