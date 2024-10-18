@extends('layouts.print')

@section('content')

<div class="invoice-box">
    <table cellpadding="0" cellspacing="0">
        <tr class="top">
            <td colspan="2">
                <table>
                    <tr>
                        <td class="title">
                            <img src="{{ public_path('images/receipt_logo.png') }}" style="width: 100%; max-width: 300px" />
                        </td>
                        <td>
                            Receipt #: {{ $receipt->number}}<br />
                            Created: {{ Carbon\Carbon::parse($receipt->created_at)->translatedFormat('l, d M Y') ?? 'No Created Time' }}<br />
                        </td>
                    </tr>

                    <tr class="information">
                        <td colspan="2">
                            <table>
                                <tr>
                                    <td>
                                        Bill to :<br />
                                        {{ $receipt->customer_name }} (Your ID: {{$receipt->customer->customer_id}})<br />
                                        {{ $receipt->customer_address }}<br />
                                        {{ $receipt->customer->contact ?? ''}}
                                    </td>

                                    <td>
                                        {{$websystem->title}}<br />
                                        {{ $receipt->teller_name }}<br />
                                        {{$websystem->email}}<br />
                                        {{$websystem->phone}}
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
                            @if ($receipt->payment_methode === 'cash')
                            Cash
                            @elseif ($receipt->payment_methode === 'transfer')
                            Transfer
                            @elseif ($receipt->payment_methode === 'online')
                            Online
                            @else
                            No Payment Methode
                            @endif
                            on {{ Carbon\Carbon::parse($receipt->payment_time)->translatedFormat('l, d M Y H:m:s') }}
                        </td>

                        <td>
                            @moneyIDR($receipt->paket_price)
                        </td>

                    </tr>


                    <tr class="heading">
                        <td>Paket</td>
                        <td>Price</td>
                    </tr>

                    <tr class="item">
                        <td>{{$receipt->paket_name}} - {{ $receipt->periode }}</td>
                        <td>
                            @moneyIDR($receipt->paket_price)
                        </td>
                    </tr>



                    <tr class="total">
                        <td></td>
                        <td>
                            <table>
                                <tr>
                                    <td>
                                        Sub Total:
                                    </td>
                                    <td>
                                        @moneyIDR($receipt->paket_price)
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
                                        @moneyIDR($receipt->paket_price + $receipt->tax)
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

    <div class="item">
        @if ($receipt->payment_methode === 'transfer')
        Payment Methode : Transfer<br />
        Bank: {{$receipt->bank_name}}<br />
        Account Name: {{ $receipt->account_name }}<br />
        Account Number: {{ $receipt->account_number }}<br />
        Reference Id: {{ $receipt->reference_id }}
        @elseif ($receipt->payment_methode === 'online')
        Payment Methode : Online<br />
        {{$receipt->bank_name}}<br />
        {{ $receipt->account_name }}<br />
        {{ $receipt->account_number }}

        @elseif ($receipt->payment_methode === 'cash')
        Payment Methode : Cash<br />
        Teller:<br />
        {{ $receipt->teller_name }}<br />

        @endif
    </div>
</div>
<div class="invoice-box">
    Struk ini merupakan bukti pembayaran yang sah dari {{$websystem->title}} dan tidak diperlukan tanda tangan basah.
</div>
@endsection