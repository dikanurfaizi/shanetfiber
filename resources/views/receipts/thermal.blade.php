@extends('layouts.thermal')

@section('content')
    <div class="container">
        <!-- Watermark -->
        <div class="watermark">
            {{ strtoupper(str_replace(['MANAGEMENT', 'Management'], '', $websystem->title)) }} </br>
            Payment Received
        </div>

        <div class="header">
            <img src="{{ public_path('images/receipt_logo.png') }}" style="width: 100%; max-width: 45mm;" />
            <div class="separator"></div>
        </div>
        <div class="receipt-info">
            <div>
                Receipt #: {{ $receipt->number }}
            </div>
            <div>
                Created: {{ Carbon\Carbon::parse($receipt->created_at)->translatedFormat('l, d M Y') ?? 'No Created Time' }}
            </div>
        </div>
        <div class="content" style="font-size: 10px">
            <div class="section">
                <div class="label">
                    Bill to :
                </div>
                <div class="value">
                    {{ $receipt->customer_name }} (Your ID: {{ $receipt->customer->customer_id }})
                </div>
                <div class="value">
                    {{ $receipt->customer_address }}
                </div>
                <div class="value">
                    {{ $receipt->customer->contact ?? '' }}
                </div>
            </div>
            <table class="table table-margin-top">
                <tr>
                    <th>Paket</th>
                    <th>Price (Rp)</th>
                </tr>
                <tr>
                    <td>{{ $receipt->paket_name }} - {{ $receipt->periode }}</td>
                    <td class="right">@moneyIDR($receipt->paket_price)</td>
                </tr>
            </table>
            <table class="table">
                <tr>
                    <td class="right">
                        Discount:
                    </td>
                    <td class="right">
                        @moneyIDR($receipt->discount ?? 0)
                    </td>
                </tr>
                <tr>
                    <td class="right">
                        Sub Total:
                    </td>
                    <td class="right">
                        @moneyIDR($receipt->price_after_discount)
                    </td>
                </tr>
                <tr>
                    <td class="right">
                        PPN:
                    </td>
                    <td class="right">
                        @moneyIDR($receipt->tax)
                    </td>
                </tr>
                <tr>
                    <td class="right">
                        <strong>
                            Total:
                        </strong>
                    </td>
                    <td class="right">
                        <strong>
                            @moneyIDR($receipt->price_after_discount + $receipt->tax)
                        </strong>
                    </td>
                </tr>
            </table>
            <table class="table" style="border-collapse: collapse; border: none;">
                <tr>
                    <td class="label" style="border: none;">Payment Method:</td>
                    <td class="value" style="border: none;">{{ ucfirst($receipt->payment_methode) }}</td>
                </tr>
                @if ($receipt->payment_methode === 'transfer')
                    <tr>
                        <td class="label" style="border: none;">Bank:</td>
                        <td class="value" style="border: none;">{{ $receipt->bank_name }}</td>
                    </tr>
                    <tr>
                        <td class="label" style="border: none;">Account Name:</td>
                        <td class="value" style="border: none;">{{ $receipt->account_name }}</td>
                    </tr>
                    <tr>
                        <td class="label" style="border: none;">Account Number:</td>
                        <td class="value" style="border: none;">{{ $receipt->account_number }}</td>
                    </tr>
                    <tr>
                        <td class="label" style="border: none;">Reference Id:</td>
                        <td class="value" style="border: none;">{{ $receipt->billing_number }}</td>
                    </tr>
                @elseif ($receipt->payment_methode === 'online')
                    <tr>
                        <td class="label" style="border: none;">Bank:</td>
                        <td class="value" style="border: none;">{{ $receipt->bank_name }}</td>
                    </tr>
                    <tr>
                        <td class="label" style="border: none;">Account Name:</td>
                        <td class="value" style="border: none;">{{ $receipt->account_name }}</td>
                    </tr>
                    <tr>
                        <td class="label" style="border: none;">Account Number:</td>
                        <td class="value" style="border: none;">{{ $receipt->account_number }}</td>
                    </tr>
                    <tr>
                        <td class="label" style="border: none;">Reference Id:</td>
                        <td class="value" style="border: none;">{{ $receipt->billing_number }}</td>
                    </tr>
                @elseif ($receipt->payment_methode === 'cash')
                    <tr>
                        <td class="label" style="border: none;">Teller:</td>
                        <td class="value" style="border: none;">{{ $receipt->teller_name }}</td>
                    </tr>
                    <tr>
                        <td class="label" style="border: none;">Reference Id:</td>
                        <td class="value" style="border: none;">{{ $receipt->billing_number }}</td>
                    </tr>
                @endif
            </table>
        </div>
        <div class="footer">
            Struk ini merupakan bukti pembayaran yang sah dari {{ strtoupper($websystem->title) }} dan tidak diperlukan
            tanda tangan
            basah. <br /> <br /> ~~~ Terimakasih ~~~
        </div>
    </div>
@endsection
