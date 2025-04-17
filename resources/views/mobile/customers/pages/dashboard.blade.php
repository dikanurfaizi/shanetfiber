<x-layouts.customers.mobile.app-layout title="Customers">

    <div class="card-header py-3 font-weight-bold text-primary">
        {{ __('customer.page.header.index') }}{{ auth()->guard('customer')->user()->name }}
    </div>
    @php
        $price = $customer->paket->price;
        $discountPrice = $price * ($customer->discount / 100);
        $priceAfterDiscount = $price - $discountPrice;
        $taxPrice = ceil($priceAfterDiscount * ($websystem->tax_rate / 100));
        $totalPrice = $priceAfterDiscount + $taxPrice;
    @endphp
    <div class="card-body col-lg-8">
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>{{ __('customer.label.id') }} :</strong>
                {{ $customer->customer_id ?? 'No ID' }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>{{ __('customer.label.paket') }} :</strong>
                {{ $customer->paket->name ?? 'No Data' }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>{{ __('customer.label.price') }} :</strong>
                <strong>@moneyIDR($price)</strong> / bulan
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>{{ __('customer.label.discount') }} :</strong>
                <strong>@moneyIDR($discountPrice)</strong> / bulan
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>{{ __('customer.label.discount-type') }} :</strong>
                <strong>{{ optional($customer->member)->type ?? 'No Discount' }}</strong>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>{{ __('customer.label.bill') }}</strong>
                @if ($websystem->enable_tax === 'true')
                    <strong>{{ __(':') }} <strong>@moneyIDR($totalPrice)</strong> / bulan
                    @else
                        <strong>{{ __(':') }} <strong>@moneyIDR($priceAfterDiscount)</strong> / bulan
                @endif
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>{{ __('customer.label.last-payment') }}</strong>
                @if ($last_payment)
                    {{ \Carbon\Carbon::parse($last_payment->payment_time)->translatedFormat('d F Y, h:i A') }}
                @else
                    {{ __('No Payment History') }}
                @endif
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>{{ __('customer.page.label.bill-not-payed') }} </strong> {{ $billings->count() }}
                {{ __('customer.label.bill') }}
            </div>
        </div>

        <table cellspacing="0" class="table">
            @foreach ($billings as $billing)
                <tr>
                    <th>{{ __('billing.table.periode') }}</th>
                    <td>{{ __(':') }} {{ $billing->billing_periode->name }}</td>
                </tr>
                <tr>
                    <th>{{ __('billing.table.paket-name') }}</th>
                    <td>{{ __(':') }} {{ $billing->paket_name }}</td>
                </tr>
                <tr>
                    <th>{{ __('billing.table.paket-price') }}</th>
                    <td>{{ __(':') }} @moneyIDR($billing->paket_price)</td>
                </tr>
                <tr>
                    <td><strong>Total bill</strong></td>
                    <td><strong>{{ __(':') }} @moneyIDR($billings->sum('paket_price'))</strong></td>
                </tr>
                <tr>
                    <th width="90px">{{ __('billing.table.action') }}</th>
                    <td class="d-flex items-center justify-content">
                        <a href="{{ route('customer.payment.show', $billing->id) }}" class="btn btn-success">
                            Bayar Online
                        </a>
                    </td>
                </tr>
            @endforeach

        </table>

        @foreach ($billings as $billing)
            <div class="card-body col-lg-8">
                <strong>Pembayaran dapat dilakukan dengan cara:</strong><br>
                1. Cash/Tunai.<br>
                2. Pembayaran Online.<br>
                3. Transfer ke rekening admin
                <span class="badge bg-primary text-white">{{ $customer->user->name }}</span><br>

                @foreach ($banks as $bank)
                    - <span class="badge bg-success text-white">
                        Bank {{ $bank->bank }} : {{ $bank->account_number }}
                    </span> a.n {{ $bank->name }}<br>
                @endforeach

                <br>
                Silahkan konfirmasi ke nomor WhatsApp <span
                    class="badge bg-primary text-white">{{ $contact_admin }}</span>
                setelah melakukan pembayaran dengan mengirimkan bukti transfer.
            </div>
        @endforeach

    </div>

</x-layouts.customers.mobile.app-layout>
