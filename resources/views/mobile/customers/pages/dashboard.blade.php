<x-layouts.customers.mobile.app-layout title="Customers">

    <div class="card-header py-3 font-weight-bold text-primary">
        {{ __('customer.page.header.index') }}{{ auth()->guard('customer')->user()->name }}
    </div>
    <div class="card-body col-lg-8">
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>{{ __('customer.label.paket') }} :</strong>
                {{ $customer->paket->name ?? 'No Data' }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>{{ __('customer.label.bill') }}</strong>
                <strong>@moneyIDR($customer->paket->price)</strong> / bulan
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12 mb-3">
            <div class="form-group">
                <strong>{{ __('customer.page.label.due-date') }}</strong>
                @if ($billings->isEmpty())
                    @if ($customer->activation_date)
                        {{ \Carbon\Carbon::parse($customer->activation_date)->addDays(30)->translatedFormat('d F Y, h:i A') }}
                    @else
                        {{ __('No Due Date') }}
                    @endif
                @else
                    @if ($customer->activation_date)
                        {{ \Carbon\Carbon::parse($customer->activation_date)->translatedFormat('d F Y, h:i A') }}
                    @else
                        {{ __('No Due Date') }}
                    @endif
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
                Pembayaran dapat dilakukan dengan cara:<br>
                1. Cash/Tunai.<br>
                2. Pembayaran Online.<br>
                3. Transfer ke rekening kami atas nama <span class="badge bg-primary text-white">Dian
                    Ardiansyah</span><br>
                <br>
                - <span class="badge bg-success text-white">Bank Mandiri : 1730012695905</span><br>
                - <span class="badge bg-success text-white">Bank BCA : 2990407315</span><br>
                - <span class="badge bg-success text-white">DANA : 085795909904</span><br>
                <br>
                Silahkan konfirmasi ke nomor WhatsApp <span class="badge bg-primary text-white">0878 1804 0024</span>
                setelah melakukan pembayaran dengan mengirimkan bukti transfer.
            </div>
        @endforeach

    </div>

</x-layouts.customers.mobile.app-layout>
