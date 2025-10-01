<x-layouts.customers.mobile.app-layout title="Customers">

    <div class="card-header py-3 bg-gradient-primary">
        <h5 class="m-0 font-weight-bold text-white">
            <i class="fas fa-user-circle mr-2"></i>
            {{ __('customer.page.header.index') }}{{ auth()->guard('customer')->user()->name }}
        </h5>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3 bg-light">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-info-circle mr-2"></i>
                            {{ __('Customer Information') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td width="40%"><strong>{{ __('customer.label.id') }}</strong></td>
                                <td>: {{ $customer->customer_id ?? 'No ID' }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('customer.label.paket') }}</strong></td>
                                <td>: {{ $customer->paket->name ?? 'No Data' }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('customer.label.price') }}</strong></td>
                                <td>: @moneyIDR($basePrice) / bulan</td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('customer.label.discount') }}</strong></td>
                                <td>: @moneyIDR($totalDiscount) / bulan</td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('customer.label.discount-type') }}</strong></td>
                                <td>: {{ optional($customer->member)->type ?? 'No Discount' }}</td>
                            </tr>
                            @if ($websystem->enable_tax === 'true')
                                <tr>
                                    <td><strong>{{ __('customer.label.tax-rate') }}</strong></td>
                                    <td>: <span class="badge bg-info text-white">@moneyIDR($taxPrice)</span></td>
                                </tr>
                            @endif
                            <tr class="bg-light">
                                <td><strong>{{ __('customer.label.bill') }}</strong></td>
                                <td>: <span class="badge bg-primary text-white">
                                        @moneyIDR($priceAfterTax)
                                        / bulan
                                    </span></td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('customer.label.last-payment') }}</strong></td>
                                <td>:
                                    @if ($lastPayment)
                                        <span
                                            class="text-danger">{{ \Carbon\Carbon::parse($lastPayment->payment_time)->translatedFormat('d F Y, h:i A') }}</span>
                                    @else
                                        {{ __('No Payment History') }}
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header py-3 bg-light">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            {{ __('Unpaid Bills') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>{{ $billings->count() }} {{ __('customer.label.bill') }}</strong>
                            {{ __('customer.page.label.bill-not-payed') }}
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th>{{ __('customer.table.no') }}</th>
                                        <th>{{ __('billing.table.periode') }}</th>
                                        <th class="text-right">{{ __('billing.table.paket-price') }}</th>
                                        @if ($websystem->payment_gateway === 'enable')
                                            <th class="text-center">{{ __('billing.table.action') }}</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($billings as $billing)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $billing->billingSubscription->billing_periode }}</td>
                                            <td class="text-right">
                                                @moneyIDR($billing->total_amount)
                                            </td>
                                            @if ($websystem->payment_gateway === 'enable')
                                                <td class="text-center">
                                                    <a href="{{ route('customer.payment.show', $billing->id) }}"
                                                        class="btn btn-sm btn-success">
                                                        <i class="fas fa-credit-card mr-1"></i>
                                                        Bayar Online
                                                    </a>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                    <tr class="bg-light">
                                        <td colspan="2" class="text-right"><strong>Total:</strong></td>
                                        <td class="text-right"><strong>@moneyIDR($billings->sum('total_amount'))</strong></td>
                                        @if ($websystem->payment_gateway === 'enable')
                                            <td></td>
                                        @endif
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow mt-4">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-money-bill-wave mr-2"></i>
                    {{ __('Payment Methods') }}
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="alert alert-info">
                            <h5 class="alert-heading"><i class="fas fa-info-circle mr-2"></i> Pembayaran dapat dilakukan
                                dengan cara:</h5>
                            <ol class="pl-3">
                                <li>Cash/Tunai</li>
                                <li>Pembayaran Online</li>
                                <li>Transfer ke rekening admin
                                    <span class="badge bg-primary text-white">{{ $customer->user->name }}</span>
                                </li>
                            </ol>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="alert alert-success">
                            <h5 class="alert-heading"><i class="fas fa-university mr-2"></i> Rekening Bank:</h5>
                            <ul class="list-unstyled">
                                @foreach ($banks as $bank)
                                    <li class="mb-2">
                                        <span class="badge bg-success text-white">
                                            Bank {{ $bank->bank }}: {{ $bank->account_number }}
                                        </span>
                                        <br>
                                        <small>a.n {{ $bank->name }}</small>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <p class="mb-0">
                        Silahkan konfirmasi ke nomor WhatsApp
                        <a href="https://wa.me/{{ $contactWhatsapp }}" target="_blank" rel="noopener noreferrer">
                            <span class="badge bg-primary text-white">{{ $contactAdmin }}</span>
                        </a>
                        setelah melakukan pembayaran dengan mengirimkan bukti transfer.
                    </p>
                </div>
            </div>
        </div>
    </div>

    @section('custom_css')
        <style>
            .table-borderless td,
            .table-borderless th {
                border: none;
            }

            .card {
                border-radius: 0.5rem;
            }

            .card-header {
                border-radius: 0.5rem 0.5rem 0 0 !important;
            }

            .badge {
                font-size: 0.85em;
                padding: 0.4em 0.6em;
            }

            .table-hover tbody tr:hover {
                background-color: rgba(0, 0, 0, 0.03);
            }
        </style>
    @endsection

</x-layouts.customers.mobile.app-layout>
