<x-layouts.customers.app-layout title="Customers">

    <div class="card-header py-3 bg-gradient-primary">
        <h5 class="m-0 font-weight-bold text-white">
            <i class="fas fa-user-circle mr-2"></i>
            {{ __('customer.page.header.index') }}{{ auth()->guard('customer')->user()->name }}
        </h5>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-lg-6">
                <div class="card shadow mb-4">
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
                                <td><strong>{{ __('customer.label.package') }}</strong></td>
                                <td>: {{ $packageName }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('customer.label.price') }}</strong></td>
                                <td>: @moneyIDR($basePrice)</td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('customer.label.discount') }}</strong></td>
                                <td>: @moneyIDR($totalDiscount)</td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('customer.label.discount-type') }}</strong></td>
                                <td>: {{ $memberName }}</td>
                            </tr>
                            @if ($websystem->enable_tax === 'true')
                                <tr>
                                    <td><strong>{{ __('customer.label.tax-rate') }}</strong></td>
                                    <td>: <span class="badge badge-info">@moneyIDR($taxPrice)</span></td>
                                </tr>
                            @endif
                            <tr class="bg-light">
                                <td><strong>{{ __('customer.label.bill') }}</strong></td>
                                <td>: <span class="badge badge-primary">
                                        @moneyIDR($priceAfterTax) / {{ $periodEn }}
                                    </span></td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('customer.page.label.due-date') }}</strong></td>
                                <td>: <span class="text-danger">{{ $dueDateText }}</span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow mb-4">
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
                                        <th>{{ __('billing.table.package-name') }}</th>
                                        <th class="text-right">{{ __('billing.table.package-price') }}</th>
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
                                            <td>{{ $packageName }}</td>
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
                                        <td colspan="3" class="text-right"><strong>Total:</strong></td>
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
                    {{ __('Metode Pembayaran') }}
                </h6>
            </div>
            <div class="card-body">
                <div class="row d-flex flex-wrap align-items-stretch">

                    <div class="col-md-6 mb-3 d-flex">
                        <div class="alert alert-info w-100 d-flex flex-column h-100 mb-0">
                            <h5 class="alert-heading">
                                <i class="fas fa-info-circle mr-2"></i> Pembayaran dapat dilakukan dengan cara:
                            </h5>
                            <ol class="pl-3 mb-0 flex-grow-1">
                                <li class="mb-2">
                                    Cash/Tunai di lokasi terdekat Anda; bisa melalui Admin
                                    <span class="badge badge-secondary">{{ $customer->user->name }}</span>
                                    atau kunjungi langsung
                                    <a href="https://maps.google.com/?q=Toko+Shanum+Cahaya+Baru+Purwakarta"
                                        target="_blank" rel="noopener noreferrer" class="font-weight-bold text-primary">
                                        Toko Shanum Cahaya Baru
                                    </a>
                                </li>
                                <li class="mb-2">Pembayaran Online via Payment Gateway</li>
                                <li class="mb-0">Transfer ke rekening/E-Wallet kami yang tertera pada halaman
                                    utama Anda</li>
                            </ol>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3 d-flex">
                        <div class="alert alert-success w-100 d-flex flex-column h-100 mb-0">
                            <h5 class="alert-heading">
                                <i class="fas fa-university mr-2"></i> Tujuan Transfer / E-Wallet:
                            </h5>
                            <ul class="list-unstyled mb-0 flex-grow-1">
                                @foreach ($banks as $bank)
                                    <li class="mb-3 p-2 border-bottom">
                                        <span class="badge badge-primary text-uppercase font-weight-bold"
                                            style="font-size: 85%;">
                                            {{ $bank->bank }}
                                        </span>

                                        <span class="d-inline-block ml-2 font-weight-bold text-dark"
                                            style="font-size: 1.15rem; letter-spacing: 0.5px;">
                                            {{ implode(' ', str_split($bank->account_number, 4)) }}
                                        </span>

                                        <br>
                                        <span class="text-muted font-italic">a.n. {{ $bank->name }}</span>
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
                            <span class="badge badge-primary">{{ $contactAdmin }}</span>
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

</x-layouts.customers.app-layout>
