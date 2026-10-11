<x-layouts.customers.mobile.app-layout title="Customers">

    <div class="card-header py-3 bg-gradient-primary rounded-top">
        <h6 class="m-0 font-weight-bold text-white d-flex align-items-center">
            <i class="fas fa-user-circle mr-2 fa-lg"></i>
            {{ __('customer.page.header.index') }} {{ auth()->guard('customer')->user()->name }}
        </h6>
    </div>

    <div class="card-body p-2 p-sm-3">
        <div class="row">
            {{-- INFORMASI PELANGGAN (REFACTORED WITH FLEXBOX LIST) --}}
            <div class="col-12 mb-3">
                <div class="card shadow-sm border-0">
                    <div class="card-header py-2 bg-light">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-info-circle mr-2"></i>
                            {{ __('Customer Information') }}
                        </h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="info-list">
                            {{-- ID Pelanggan --}}
                            <div class="info-item d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span class="text-muted font-weight-bold small">{{ __('customer.label.id') }}</span>
                                <span class="font-weight-bold text-dark text-right text-break pl-2">
                                    {{ $customer->customer_id ?? 'No ID' }}
                                </span>
                            </div>

                            {{-- Paket --}}
                            <div class="info-item d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span
                                    class="text-muted font-weight-bold small">{{ __('customer.label.package') }}</span>
                                <span class="font-weight-bold text-primary text-right text-break pl-2">
                                    {{ $packageName }}
                                </span>
                            </div>

                            {{-- Harga Paket --}}
                            <div class="info-item d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span class="text-muted font-weight-bold small">{{ __('customer.label.price') }}</span>
                                <span class="text-dark text-right pl-2">
                                    @moneyIDR($basePrice)
                                </span>
                            </div>

                            {{-- Diskon --}}
                            <div class="info-item d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span
                                    class="text-muted font-weight-bold small">{{ __('customer.label.discount') }}</span>
                                <span class="text-dark text-right pl-2">
                                    @moneyIDR($totalDiscount)
                                </span>
                            </div>

                            {{-- Tipe Diskon --}}
                            <div class="info-item d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span
                                    class="text-muted font-weight-bold small">{{ __('customer.label.discount-type') }}</span>
                                <span class="text-dark text-right text-break pl-2">
                                    {{ $memberName }}
                                </span>
                            </div>

                            {{-- Pajak (Jika Aktif) --}}
                            @if (($websystem->enable_tax ?? false) === 'true' || ($websystem->enable_tax ?? false) === true)
                                <div
                                    class="info-item d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <span
                                        class="text-muted font-weight-bold small">{{ __('customer.label.tax-rate') }}</span>
                                    <span class="badge bg-info text-white">@moneyIDR($taxPrice ?? 0)</span>
                                </div>
                            @endif

                            {{-- Tagihan Bulanan --}}
                            <div
                                class="info-item d-flex justify-content-between align-items-center py-2 border-bottom bg-light rounded px-2 my-1">
                                <span class="text-dark font-weight-bold small">{{ __('customer.label.bill') }}</span>
                                <span class="badge bg-primary text-white font-weight-bold p-2 text-wrap text-right">
                                    @moneyIDR($priceAfterTax) / {{ $periodEn ?? 'bulan' }}
                                </span>
                            </div>

                            {{-- Tanggal Jatuh Tempo --}}
                            <div class="info-item d-flex justify-content-between align-items-start py-2 border-bottom">
                                <span
                                    class="text-muted font-weight-bold small">{{ __('customer.page.label.due-date') }}</span>
                                <span class="text-danger font-weight-bold text-right text-break pl-2">
                                    {{ $dueDateText }}
                                </span>
                            </div>

                            {{-- Pembayaran Terakhir --}}
                            <div class="info-item d-flex justify-content-between align-items-start py-2">
                                <span
                                    class="text-muted font-weight-bold small">{{ __('customer.label.last-payment') }}</span>
                                <span class="text-right text-break pl-2">
                                    @if ($lastPayment)
                                        <span class="text-success font-weight-bold">
                                            {{ \Carbon\Carbon::parse($lastPayment->payment_time)->translatedFormat('d F Y, H:i') }}
                                            WIB
                                        </span>
                                    @else
                                        <span class="text-muted small">{{ __('No Payment History') }}</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAGIHAN BELUM DIBAYAR --}}
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header py-2 bg-light">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            {{ __('Unpaid Bills') }}
                        </h6>
                    </div>
                    <div class="card-body p-2 p-sm-3">
                        <div class="alert alert-warning p-2 small mb-3">
                            <i class="fas fa-info-circle mr-1"></i>
                            <strong>{{ $billings->count() }} {{ __('customer.label.bill') }}</strong>
                            {{ __('customer.page.label.bill-not-payed') }}
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-sm small mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>{{ __('customer.table.no') }}</th>
                                        <th>{{ __('billing.table.periode') }}</th>
                                        <th class="text-right">{{ __('billing.table.package-price') }}</th>
                                        @if ($websystem->payment_gateway === 'enable')
                                            <th class="text-center">{{ __('billing.table.action') }}</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($billings as $billing)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td class="text-nowrap">
                                                {{ $billing->billingSubscription->billing_periode ?? '-' }}</td>
                                            <td class="text-right font-weight-bold">
                                                @moneyIDR($billing->total_amount)
                                            </td>
                                            @if ($websystem->payment_gateway === 'enable')
                                                <td class="text-center">
                                                    <a href="{{ route('customer.payment.show', $billing->id) }}"
                                                        class="btn btn-xs btn-success px-2 py-1">
                                                        <i class="fas fa-credit-card mr-1"></i> Bayar
                                                    </a>
                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $websystem->payment_gateway === 'enable' ? 4 : 3 }}"
                                                class="text-center text-muted py-3">
                                                Tidak ada tagihan aktif
                                            </td>
                                        </tr>
                                    @endforelse
                                    <tr class="bg-light font-weight-bold">
                                        <td colspan="2" class="text-right">Total:</td>
                                        <td class="text-right text-primary">@moneyIDR($billings->sum('total_amount'))</td>
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

        {{-- METODE PEMBAYARAN & REKENING --}}
        <div class="card shadow-sm border-0 mt-3">
            <div class="card-header py-2 bg-light">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-money-bill-wave mr-2"></i>
                    {{ __('Metode Pembayaran') }}
                </h6>
            </div>
            <div class="card-body p-3">
                <div class="alert alert-info p-2 small mb-3">
                    <h6 class="alert-heading font-weight-bold mb-2 small">
                        <i class="fas fa-info-circle mr-1"></i> Pembayaran dapat dilakukan via:
                    </h6>
                    <ol class="pl-3 mb-0">
                        <li>Cash/Tunai di Admin <span
                                class="badge bg-secondary text-white">{{ $customer->user->name ?? 'Admin' }}</span>
                        </li>
                        <li>Pembayaran Online via Payment Gateway</li>
                        <li>Transfer ke Rekening Resmi</li>
                    </ol>
                </div>

                <div class="alert alert-success p-2 mb-0">
                    <h6 class="alert-heading font-weight-bold mb-2 small">
                        <i class="fas fa-university mr-1"></i> Rekening Bank / E-Wallet:
                    </h6>
                    <ul class="list-unstyled mb-0 small">
                        @forelse ($banks as $bank)
                            <li class="mb-2 pb-2 border-bottom">
                                <span class="badge bg-success text-white text-uppercase">
                                    {{ $bank->bank }}
                                </span>
                                <strong class="ml-1 text-dark d-inline-block" style="letter-spacing: 0.5px;">
                                    {{ implode(' ', str_split($bank->account_number, 4)) }}
                                </strong>
                                <br>
                                <small class="text-muted">a.n {{ $bank->name }}</small>
                            </li>
                        @empty
                            <li class="text-muted small">Tidak ada informasi rekening bank</li>
                        @endforelse
                    </ul>
                </div>

                <div class="text-center mt-3">
                    <p class="mb-0 text-muted small">
                        Silakan konfirmasi ke nomor WhatsApp
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
            .info-list .info-item:last-child {
                border-bottom: none !important;
            }

            .text-break {
                word-break: break-word;
            }

            .btn-xs {
                padding: 0.25rem 0.4rem;
                font-size: 0.75rem;
                line-height: 1.2;
                border-radius: 0.2rem;
            }

            /* Sembunyikan horizontal scroll pada mobile container */
            .card-body {
                overflow-x: hidden;
            }
        </style>
    @endsection

</x-layouts.customers.mobile.app-layout>
