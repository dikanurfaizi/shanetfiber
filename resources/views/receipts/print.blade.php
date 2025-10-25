@extends('layouts.print')

@section('content')
    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0">
            <tr class="top">
                <td colspan="2">
                    <table>
                        <tr>
                            <td class="title">
                                <img src="{{ public_path('images/receipt_logo.png') }}"
                                    style="width: 100%; max-width: 150px" />
                            </td>
                            <td>
                                Receipt #: {{ $receipt->number }}<br />
                                {{ Carbon\Carbon::parse($receipt->created_at)->translatedFormat('d M Y') ?? 'No Created Time' }}<br />
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="information">
                <td colspan="2">
                    <table>
                        <tr>
                            <td>
                                Bill to : {{ $receipt->customer_name }} (ID: {{ $receipt->customer->customer_id }})<br />
                                {{ $receipt->customer_address }}<br />
                                {{ $receipt->customer->contact ?? '' }}
                            </td>
                            <td>
                                {{ $websystem->title }}<br />
                                {{ $receipt->teller_name }}<br />
                                {{ $websystem->email }}<br />
                                {{ $websystem->phone }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="heading">
                <td>Payment Method</td>
                <td>
                    @if ($receipt->payment_methode === 'cash')
                        Cash#
                    @elseif ($receipt->payment_methode === 'transfer')
                        Transfer#
                    @elseif ($receipt->payment_methode === 'online')
                        Online#
                    @else
                        No Payment Methode#
                    @endif
                </td>
            </tr>

            <tr class="details">
                <td>
                    {{ ucfirst($receipt->payment_methode) }} on
                    {{ Carbon\Carbon::parse($receipt->payment_time)->translatedFormat('d M Y H:i:s') }}
                </td>
                <td>@moneyIDR($receipt->package_price)</td>
            </tr>

            <tr class="heading">
                <td>Package</td>
                <td>Price</td>
            </tr>

            <tr class="item">
                <td>{{ $receipt->package_name }} - {{ $receipt->periode }}</td>
                <td>@moneyIDR($receipt->package_price)</td>
            </tr>

            <tr class="total">
                <td></td>
                <td>
                    <table>
                        <tr>
                            <td>
                                Discount:
                            </td>
                            <td>
                                @moneyIDR($receipt->discount)
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Sub Total:
                            </td>
                            <td>
                                @moneyIDR($receipt->price_after_discount)
                            </td>
                        </tr>
                        <tr>
                            <td>
                                PPN:
                            </td>
                            <td>
                                @moneyIDR($receipt->tax)
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Total:
                            </td>
                            <td>
                                @moneyIDR($receipt->price_after_discount + $receipt->tax)
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table class="payment-details">
            @if ($receipt->payment_methode === 'transfer')
                <tr>
                    <td>Payment Method</td>
                    <td>Transfer</td>
                </tr>
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
                <tr>
                    <td>Reference Id</td>
                    <td>{{ $receipt->billing_number }}</td>
                </tr>
            @elseif ($receipt->payment_methode === 'online')
                <tr>
                    <td>Payment Method</td>
                    <td>Online</td>
                </tr>
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
            @elseif ($receipt->payment_methode === 'cash')
                <tr>
                    <td>Payment Method</td>
                    <td>Cash</td>
                </tr>
                <tr>
                    <td>Teller</td>
                    <td>{{ $receipt->teller_name }}</td>
                </tr>
                <tr>
                    <td>Reference Id</td>
                    <td>{{ $receipt->billing_number }}</td>
                </tr>
            @endif
        </table>
    </div>
    <div class="invoice-box" style="text-align: center; font-size: 12px;">
        Struk ini adalah bukti pembayaran sah dari {{ strtoupper($websystem->title) }} dan tidak memerlukan tanda tangan
        basah.
    </div>
    <div
        style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 80px; color: rgba(0, 0, 255, 0.1); font-weight: bold;">
        {{ strtoupper($websystem->title) }}
    </div>
@endsection
