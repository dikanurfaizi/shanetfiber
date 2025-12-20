<x-layouts.cpanel.billings.app-layout title="Billing Show">
    <div class="card-body col-lg-8">

        <!-- Customer Information -->
        <h6 class="text-primary mb-3">
            <i class="fas fa-user"></i> Customer Information
        </h6>

        <table class="table table-sm table-borderless align-middle">
            <tr>
                <th style="width: 30%" class="text-muted">Customer ID</th>
                <td>
                    <span class="badge bg-success text-white">
                        {{ $billing->customer->customer_id ?? '-' }}
                    </span>
                </td>
            </tr>

            <tr>
                <th class="text-muted">Name</th>
                <td>
                    <a href="{{ route('customers.show', $billing->customer->slug) }}" class="text-decoration-none">
                        {{ $billing->customer->name ?? '-' }}
                    </a>
                </td>
            </tr>

            <tr>
                <th class="text-muted">Address</th>
                <td>{{ $billing->customer->address ?? '-' }}</td>
            </tr>
        </table>


        <!-- Package Information -->
        <h6 class="text-primary mt-4 mb-3">
            <i class="fas fa-box"></i> Package Information
        </h6>

        <table class="table table-sm table-borderless align-middle">
            <tr>
                <th style="width: 30%" class="text-muted">Package</th>
                <td>{{ $customerSubscription->package->name ?? '-' }}</td>
            </tr>

            <tr>
                <th class="text-muted">Price</th>
                <td>
                    @if ($customerSubscription->package->price == 0)
                        Free
                    @else
                        @moneyIDR($customerSubscription->package->price)
                    @endif
                </td>
            </tr>

            <tr>
                <th class="text-muted">Subscription</th>
                <td>{{ $customerSubscription->packageDuration->duration->name_en }}</td>
            </tr>
        </table>


        <!-- Membership Information -->
        <h6 class="text-primary mt-4 mb-3">
            <i class="fas fa-id-badge"></i> Membership Information
        </h6>

        <table class="table table-sm table-borderless align-middle">
            <tr>
                <th style="width: 30%" class="text-muted">Registered As</th>
                <td>{{ $memberDisplay }}</td>
            </tr>

            <tr>
                <th class="text-muted">Discount</th>
                <td>@moneyIDR($discount)</td>
            </tr>

            <tr>
                <th class="text-muted">End Date</th>
                <td>{{ $endDate }}</td>
            </tr>
        </table>

    </div>

    <div class="card-footer">
        <a class="btn btn-sm btn-secondary" href="{{ route('billings.index') }}"><i class="fa fa-arrow-left"></i>
            Back</a>
    </div>

    @if ($billings)
        <div class="card-body col-lg-auto">
            @php
                $alertClass = $totalUnpaidBill > 0 ? 'alert-warning' : 'alert-success';
                $icon = $totalUnpaidBill > 0 ? 'fa-exclamation-triangle' : 'fa-check-circle';
            @endphp

            <div class="alert {{ $alertClass }} d-flex align-items-center gap-2">
                <i class="fas {{ $icon }}"></i>

                <span class="ml-2">
                    This customer has
                    <strong>{{ $totalUnpaidBill }}</strong>
                    outstanding payment{{ $totalUnpaidBill == 1 ? '' : 's' }}.
                </span>
            </div>


            <div class="row mt-4 mb-3">
                <div class="col-md-6 mb-3">
                    <div class="p-3 rounded border bg-light">
                        <div class="text-muted">Total Arrears</div>
                        <div class="fs-4 text-danger fw-bold">
                            @moneyIDR($totalUnpaidAmount)
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="p-3 rounded border bg-light">
                        <div class="text-muted">Total Paid</div>
                        <div class="fs-4 text-success fw-bold">
                            @moneyIDR($totalPaidAmount)
                        </div>
                    </div>
                </div>
            </div>

            <div class="responsive-table">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Period</th>
                            <th>Package</th>
                            <th>Total</th>
                            <th>Status</th>
                            @can('billing-edit')
                                <th>Action</th>
                            @endcan
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($billings as $idx => $item)
                            <tr class="expand-row {{ $item->status !== 'LS' ? 'table-light border-danger' : '' }}"
                                data-id="row-{{ $item->id }}">
                                <td class="expand-toggle">
                                    <i class="fas fa-chevron-right"></i>
                                    #{{ $billings->firstItem() + $idx }}
                                </td>

                                <td>
                                    <span class="text-primary">
                                        {{ $item->billingSubscription->billingPeriode->name }}
                                    </span>
                                </td>

                                <td>
                                    <span
                                        class="badge bg-success text-white">{{ $item->billingSubscription->customerSubscription->package->name }}</span>
                                </td>

                                <td>@moneyIDR($item->total_amount)</td>

                                <td>
                                    @include('components.billing-status-badge', [
                                        'status' => $item->status,
                                    ])
                                </td>

                                @can('billing-edit')
                                    <td class="align-middle action-col" data-label="Action">
                                        <div class="d-flex justify-content-start align-items-center">

                                            {{-- =====================
                                            PAY / PRINT
                                            ===================== --}}
                                            @if ($item->status === 'BL' || $item->status === 'PL')
                                                {!! Form::model($item, [
                                                    'method' => 'PATCH',
                                                    'route' => ['billings.payment', $item->slug],
                                                    'class' => 'mr-2', // ⬅️ PENTING
                                                ]) !!}
                                                <button type="submit" class="btn btn-success btn-sm btn-action-primary">
                                                    <i class="fas fa-money-bill-wave"></i>
                                                    {{ __('billing.button.pay') }}
                                                </button>
                                                {!! Form::close() !!}

                                                <a class="btn btn-outline-primary btn-sm btn-action-secondary"
                                                    target="_blank"
                                                    href="{{ route('billings.print-invoice', $billing->slug) }}">
                                                    <i class="fas fa-file-pdf"></i>
                                                    Invoice
                                                </a>

                                                {{-- =====================
                                                UNPAY / RECEIPT
                                                ===================== --}}
                                            @elseif ($item->status === 'LS' && $item->total_amount > 0)
                                                {!! Form::model($billing, [
                                                    'method' => 'PATCH',
                                                    'route' => ['billings.unpayment', $billing->slug],
                                                    'class' => 'mr-2',
                                                ]) !!}
                                                <button type="submit" class="btn btn-danger btn-sm btn-action-primary">
                                                    <i class="fas fa-money-bill-wave"></i>
                                                    {{ __('billing.button.unpay') }}
                                                </button>
                                                {!! Form::close() !!}

                                                @if (!empty($item->formatted['receipt_slug']))
                                                    <a href="{{ route('receipts.show', $item->formatted['receipt_slug']) }}"
                                                        class="btn btn-outline-primary btn-sm btn-action-secondary">
                                                        <i class="fa fa-eye"></i>
                                                        Receipt
                                                    </a>
                                                @endif
                                            @endif

                                        </div>
                                    </td>
                                @endcan
                            </tr>

                            <tr id="row-{{ $item->id }}" class="expand-content">
                                <td colspan="6">
                                    <div class="p-3">

                                        {{-- STATUS PAID --}}
                                        @if ($item->status === 'LS')
                                            <div><strong>Paid at:</strong> {{ $item->formatted['payment_time'] }}</div>

                                            @if ($item->formatted['method'])
                                                <div><strong>Method:</strong> {{ $item->formatted['method'] }}</div>

                                                @if ($item->formatted['method'] == 'Transfer')
                                                    <div><strong>To Bank:</strong> {{ $item->formatted['bank_name'] }}
                                                    </div>
                                                    <div><strong>In the name of:</strong>
                                                        {{ $item->formatted['account_name'] }}</div>
                                                    <div><strong>Account Number:</strong>
                                                        {{ $item->formatted['account_number'] }}</div>
                                                @elseif($item->formatted['method'] == 'Cash')
                                                    <div><strong>Cashier User:</strong>
                                                        {{ $item->formatted['paid_by'] }}</div>
                                                @endif
                                            @endif

                                            @if ($item->formatted['receipt_slug'])
                                                <div>
                                                    <strong>Receipt:</strong>
                                                    <a
                                                        href="{{ route('receipts.show', $item->formatted['receipt_slug']) }}">
                                                        Receipt
                                                    </a>
                                                </div>
                                            @endif
                                        @endif

                                        {{-- STATUS PAY LATER --}}
                                        @if ($item->status === 'PL')
                                            <div><strong>Pay Later Date:</strong> {{ $item->formatted['pay_later'] }}
                                            </div>
                                        @endif

                                        {{-- STATUS UNPAID --}}
                                        @if ($item->status === 'BL')
                                            <div><strong>Due Date:</strong> {{ $item->formatted['due_date'] }}</div>
                                        @endif

                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- PAGINATION --}}
                <div class="mt-3">
                    {{ $billings->links() }}
                </div>
            </div>
        </div>
    @endif

    @section('custom_styles')
        <style>
            .expand-row {
                cursor: pointer;
            }

            .expand-content {
                display: none;
                background: #f8f9fa;
            }

            .expand-toggle i {
                transition: transform 0.2s ease;
                color: #6c757d;
            }

            .rotate-90 {
                transform: rotate(90deg);
            }

            .expand-row:hover {
                background-color: #f8f9fa;
                cursor: pointer;
            }

            .btn-action-primary {
                min-width: 110px;
                /* Pay / Unpay */
                text-align: center;
            }

            .btn-action-secondary {
                min-width: 140px;
                /* Print Invoice / View Receipt */
                text-align: center;
            }

            @media (max-width: 767px) {
                .responsive-table td.action-col {
                    display: block !important;
                }

                .responsive-table td.action-col>div {
                    padding-left: 12px;
                    /* 🔑 visual anchor */
                }

                .responsive-table table tr {
                    padding: 8px;
                }

                .responsive-table table td {
                    padding: 4px 0 !important;
                }

                .expand-content {
                    margin-top: 8px;
                    border-top: 1px dashed #dee2e6;
                }

                .action-col .d-flex {
                    flex-direction: column;
                    align-items: flex-start;
                }

                .action-col .btn {
                    width: 100%;
                }

                /* Action container */
                .responsive-table td.action-col .d-flex {
                    flex-direction: column;
                    align-items: stretch;
                    /* 🔑 bukan flex-start */
                    gap: 8px;
                    /* jarak konsisten */
                }

                /* Semua tombol action */
                .responsive-table td.action-col .btn {
                    width: 100%;
                    /* full width */
                    justify-content: center;
                    /* icon + text center */
                }

                /* Secondary action lebih “ringan” */
                .responsive-table td.action-col .btn-outline-primary {
                    opacity: 0.95;
                }

                .responsive-table table thead {
                    display: none;
                    /* hide header */
                }

                .responsive-table table,
                .responsive-table table tbody,
                .responsive-table table tr,
                .responsive-table table td {
                    display: block;
                    width: 100%;
                }

                .responsive-table table tr {
                    margin-bottom: 1rem;
                    border: 1px solid #dee2e6;
                    border-radius: 8px;
                    padding: 10px;
                    background: #fff;
                    box-shadow: 0 1px 3px rgb(0 0 0 / 10%);
                }

                .responsive-table table td {
                    border: none !important;
                    padding: 6px 0 !important;
                }

                .responsive-table table td::before {
                    content: attr(data-label);
                    font-weight: 600;
                    margin-right: 6px;
                    display: block;
                    color: #6c757d;
                    font-size: 0.75rem;
                }
            }
        </style>
    @endsection

    @section('custom_scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // document.querySelectorAll('.expand-row').forEach(row => {
                //     row.addEventListener('click', function(e) {
                //         const target = document.getElementById(this.dataset.id);
                //         const icon = this.querySelector('.expand-toggle i');

                //         // Toggle visibility
                //         const isOpen = target.style.display === 'table-row';
                //         target.style.display = isOpen ? 'none' : 'table-row';

                //         // Toggle icon rotation
                //         if (isOpen) {
                //             icon.classList.remove('rotate-90');
                //         } else {
                //             icon.classList.add('rotate-90');
                //         }
                //     });
                // });

                document.querySelectorAll('.expand-toggle').forEach(toggle => {
                    toggle.addEventListener('click', function(e) {
                        e.stopPropagation();

                        const row = this.closest('tr');
                        const target = document.getElementById(row.dataset.id);
                        const icon = this.querySelector('i');

                        const isOpen = target.style.display === 'block';
                        target.style.display = isOpen ? 'none' : 'block';
                        icon.classList.toggle('rotate-90', !isOpen);
                    });
                });

                document.querySelectorAll('.expand-content').forEach(el => {
                    el.style.display = 'none';
                });
            });
        </script>
    @endsection
</x-layouts.cpanel.billings.app-layout>
