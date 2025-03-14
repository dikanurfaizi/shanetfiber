<x-layouts.customers.app-layout title="Customers">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">
            {{ __('customer.page.header.index') }}{{ auth()->guard('customer')->user()->name }}</h5>

    </div>

    @php
        $price = $customer->paket->price;
        $discountPrice = $price * ($customer->discount / 100);
        $priceAfterDiscount = $price - $discountPrice;
        $taxPrice = ceil($priceAfterDiscount * ($websystem->tax_rate / 100));
        $totalPrice = $priceAfterDiscount + $taxPrice;
    @endphp
    <div class="card-body col-lg-12">
        <div style="overflow-x: auto;">
            <table id="serverTable" cellspacing="0" width="100%" class="table no-footer">

                <tr class="table-borderless">
                    <td width="100px"><strong>{{ __('customer.label.id') }}</strong></td>
                    <td width="380px">{{ __(':') }} {{ $customer->customer_id ?? 'No ID' }}</td>
                </tr>
                <tr class="table-borderless">
                    <td width="100px"><strong>{{ __('customer.label.paket') }}</strong></td>
                    <td width="380px">{{ __(':') }} {{ $customer->paket->name ?? 'No Data' }}</td>
                </tr>
                <tr class="table-borderless">
                    <td width="100px"><strong>{{ __('customer.label.price') }}</strong></td>
                    <td width="380px">{{ __(':') }} @moneyIDR($price) </td>
                </tr>

                <tr class="table-borderless">
                    <td width="100px"><strong>{{ __('customer.label.discount') }}</strong></td>
                    <td width="380px">{{ __(':') }} @moneyIDR($discountPrice) </td>
                </tr>
                <tr class="table-borderless">
                    <td width="100px"><strong>{{ __('customer.label.discount-type') }}</strong></td>
                    <td width="380px">{{ __(':') }} {{ $customer->member->type ?? 'No Data' }} </td>
                </tr>
                @if ($websystem->enable_tax === 'true')
                    <tr class="table-borderless">
                        <td width="100px"><strong>{{ __('customer.label.tax-rate') }}</strong></td>
                        <td width="380px">{{ __(':') }} <strong>@moneyIDR($taxPrice)</strong></td>
                    </tr>
                @endif
                <tr class="table-borderless">
                    <td width="100px"><strong>{{ __('customer.label.bill') }}</strong></td>
                    @if ($websystem->enable_tax === 'true')
                        <td width="380px">{{ __(':') }} <strong>@moneyIDR($totalPrice)</strong> / month</td>
                    @else
                        <td width="380px">{{ __(':') }} <strong>@moneyIDR($priceAfterDiscount)</strong> / month</td>
                    @endif
                </tr>
                <tr class="table-borderless">
                    <td><strong>{{ __('customer.page.label.due-date') }}</strong></td>
                    <td>{{ __(':') }}
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
                    </td>
                </tr>
                {{-- <tr class="table-borderless">
                    <td><strong>{{ __('customer.label.last-payment') }}</strong></td>
                    <td>{{ __(':') }}
                        @if ($last_payment)
                            {{ \Carbon\Carbon::parse($last_payment->payment_time)->translatedFormat('d F Y, h:i A') }}
                        @else
                            {{ __('No Payment History') }}
                        @endif
                    </td>
                </tr> --}}

            </table>
            <strong>{{ __('customer.page.label.bill-not-payed') }}</strong>{{ $billings->count() }}
            {{ __('customer.label.bill') }}

            <div style="overflow-x: auto;">
                <table id="customersTable" cellspacing="0" width="100%"
                    class="table  datatable-loading no-footer sortable searchable">
                    <tr>
                        <th>{{ __('customer.table.no') }}</th>
                        <th>{{ __('billing.table.periode') }}</th>
                        <th>{{ __('billing.table.paket-name') }}</th>
                        <th>{{ __('billing.table.paket-price') }}</th>
                        <th width="180px">{{ __('billing.table.action') }}</th>
                    </tr>
                    @foreach ($billings as $billing)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $billing->billing_periode->name }}</td>
                            <td>{{ $billing->paket_name }}</td>
                            <td>
                                @moneyIDR($billing->paket_price + $billing->tax)
                            </td>
                            <td class="d-flex items-center justify-content">
                                <a href="{{ route('customer.payment.show', $billing->id) }}"
                                    class="btn btn-sm btn-success">
                                    Bayar Online
                                </a>
                            </td>
                        </tr>
                    @endforeach

                    <tr>
                        <td></td>
                        <td></td>
                        <td><strong>Total Price</strong></td>
                        <td><strong>: @moneyIDR($billings->sum('paket_price') + $billings->sum('tax'))</strong></td>
                    </tr>


                </table>
            </div>

            @foreach ($billings as $billing)
                <div class="card-body col-lg-12">
                    <strong>Pembayaran dapat dilakukan dengan cara:</strong><br>
                    1. Cash/Tunai.<br>
                    2. Pembayaran Online.<br>
                    3. Transfer ke rekening admin area Purwakarta <span class="badge bg-primary text-white">Dian
                        Ardiansyah</span><br>
                    - <span class="badge bg-success text-white">Bank BCA : 299 040 7315</span><br>
                    - <span class="badge bg-success text-white">Bank Mandiri : 173-00-1269590-5</span><br>
                    - <span class="badge bg-success text-white">Dana : 0857-9590-9904</span><br>
                    <br>
                    4. Transfer ke rekening admin area Babakancikao <span class="badge bg-primary text-white">Budi
                        Kosasih</span><br>
                    - <span class="badge bg-success text-white">Bank Mandiri : 173-00-1265603-0</span><br>
                    - <span class="badge bg-success text-white">Dana : 0857-1084-0198</span><br>
                    <br>
                    Silahkan konfirmasi ke nomor WhatsApp <span
                        class="badge bg-primary text-white">0856-4233-6774</span> setelah melakukan pembayaran dengan
                    mengirimkan bukti transfer.
                </div>
            @endforeach

        </div>

</x-layouts.customers.app-layout>
