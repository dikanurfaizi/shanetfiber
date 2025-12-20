{{-- DESKTOP SUMMARY --}}
<div class="d-none d-md-block">
    <div style="overflow-x:auto;">
        <table class="table table-borderless no-footer">
            <tr>
                <td><strong>Total Bill</strong></td>
                <td>: @moneyIDR($totalAmount)</td>

                <td><strong>Unpaid Amount</strong></td>
                <td>: @moneyIDR($notYetPaid)</td>
            </tr>
            <tr>
                <td><strong>Last Payment Paid</strong></td>
                <td>: @moneyIDR($lastPayment)</td>

                <td><strong>Last Payment Date</strong></td>
                <td>:
                    {{ $lastPaymentTime ? \Carbon\Carbon::parse($lastPaymentTime)->format('d F Y') : 'No Payment' }}
                </td>
            </tr>
            <tr>
                <td><strong>Due Date</strong></td>
                <td>:
                    {{ $dueDate ? \Carbon\Carbon::parse($dueDate)->format('d F Y') : 'No Due Date' }}
                </td>

                <td><strong>Discount Membership</strong></td>
                <td>: @moneyIDR($calculated_discount)</td>
            </tr>
            <tr>
                <td><strong>Membership Status</strong></td>
                <td>: {{ $is_member ? $membership_name : 'Non Member' }}</td>

                <td><strong>Membership Period</strong></td>
                <td>:
                    @if (!$is_member)
                        —
                    @elseif($start_date && !$end_date)
                        {{ \Carbon\Carbon::parse($start_date)->format('d F Y') }} —
                        <span class="text-success">Unlimited</span>
                    @elseif($start_date && $end_date)
                        {{ \Carbon\Carbon::parse($start_date)->format('d F Y') }} —
                        {{ \Carbon\Carbon::parse($end_date)->format('d F Y') }}
                    @else
                        —
                    @endif
                </td>
            </tr>
        </table>
    </div>
</div>

{{-- MOBILE SUMMARY --}}
<div class="d-block d-md-none mobile-summary">
    <div class="summary-row">
        <span>Total Bill</span>
        <strong>@moneyIDR($totalAmount)</strong>
    </div>
    <div class="summary-row">
        <span>Unpaid Amount</span>
        <strong>@moneyIDR($notYetPaid)</strong>
    </div>
    <div class="summary-row">
        <span>Last Payment</span>
        <strong>@moneyIDR($lastPayment)</strong>
    </div>
    <div class="summary-row">
        <span>Payment Date</span>
        <strong>{{ $lastPaymentTime ? \Carbon\Carbon::parse($lastPaymentTime)->format('d M Y') : '-' }}</strong>
    </div>
    <div class="summary-row">
        <span>Due Date</span>
        <strong>{{ $dueDate ? \Carbon\Carbon::parse($dueDate)->format('d M Y') : '-' }}</strong>
    </div>
    <div class="summary-row">
        <span>Membership</span>
        <strong>{{ $is_member ? $membership_name : 'Non Member' }}</strong>
    </div>
</div>


{{-- MOBILE STATUS (SETELAH SUMMARY, SEBELUM BREAKDOWN) --}}
<div class="d-block d-md-none text-center my-4">
    <div class="billing-stamp-mobile {{ strtolower($billingStatus) }}">
        {{ $stampText }}
    </div>
</div>

<hr class="my-4">

<h5 class="mb-3 ms-1">
    <i class="ti ti-list-details"></i> Billing Breakdown
</h5>

<div class="row">

    {{-- LEFT --}}
    <div class="col-md-6">

        {{-- MOBILE --}}
        <div class="d-block d-md-none mobile-billing-list">
            @foreach ($packageItems as $item)
                <div class="breakdown-item">
                    <div class="d-flex justify-content-between">
                        <div>
                            <strong>{{ $item->item_name }}</strong>
                            <div class="text-muted small">Package</div>
                        </div>
                        <div class="text-end">
                            <div class="currency small">Rp</div>
                            <div class="amount">{{ number_format($item->subtotal, 2, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            @endforeach

            @foreach ($addonItems as $item)
                <div class="breakdown-item">
                    <div class="d-flex justify-content-between">
                        <div>
                            {{ $item->item_name }}
                            <div class="text-muted small">Addon × {{ $item->qty }}</div>
                        </div>
                        <div class="text-end">
                            <div class="currency small">Rp</div>
                            <div class="amount">{{ number_format($item->subtotal, 2, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            @endforeach

            @foreach ($discountItems as $item)
                <div class="breakdown-item discount-item">
                    <div class="d-flex justify-content-between">
                        <div>
                            {{ $item->item_name }}
                            <div class="small text-danger">Discount</div>
                        </div>
                        <div class="text-end text-danger">
                            <div class="currency small">Rp</div>
                            <div class="amount">{{ number_format($item->subtotal, 2, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="mobile-total-box">
                <div class="d-flex justify-content-between align-items-center">
                    <strong>Total</strong>
                    <div class="text-end">
                        <div class="currency small"><strong>Rp</strong></div>
                        <div class="amount"><strong>{{ number_format($totalAmount, 2, ',', '.') }}</strong></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- DESKTOP --}}
        <div class="d-none d-md-block">
            <table class="table table-sm table-borderless billing-breakdown-table">
                <tbody>
                    @foreach ($packageItems as $item)
                        <tr>
                            <td><strong>{{ $item->item_name }}</strong>
                                <div class="text-muted small">Package</div>
                            </td>
                            <td class="currency">Rp</td>
                            <td class="amount">{{ number_format($item->subtotal, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    @foreach ($addonItems as $item)
                        <tr>
                            <td>{{ $item->item_name }} <span class="text-muted small">(Addon x
                                    {{ $item->qty }})</span></td>
                            <td class="currency">Rp</td>
                            <td class="amount">{{ number_format($item->subtotal, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    @foreach ($discountItems as $item)
                        <tr class="text-danger">
                            <td>{{ $item->item_name }}<div class="text-muted small">Discount</div>
                            </td>
                            <td class="currency">Rp</td>
                            <td class="amount">{{ number_format($item->subtotal, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    <tr class="border-top">
                        <td><strong>Total</strong></td>
                        <td class="currency"><strong>Rp</strong></td>
                        <td class="amount"><strong>{{ number_format($totalAmount, 2, ',', '.') }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- RIGHT DESKTOP STAMP --}}
    <div class="col-md-6 d-none d-md-flex align-items-center justify-content-center">
        <div class="billing-stamp {{ strtolower($billingStatus) }}">
            {{ $stampText }}
        </div>
    </div>
</div>


{{-- ACTION: EDIT BILLING --}}
@can('billing-edit', $customer)
    @if (!$isEdit)
        <div class="mt-3 text-end">
            <a href="{{ route('customers.show', [$customer->slug, 'tab' => 'billing', 'edit' => 1]) }}"
                class="btn-soft btn-blue">
                <i class="fa fa-edit"></i> Edit Billing
            </a>
        </div>
    @endif
@endcan
</div>

<style>
    /* ===== SUMMARY MOBILE ===== */
    .mobile-summary {
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding-bottom: 16px;
        border-bottom: 1px solid #e5e7eb;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        font-size: 14px;
    }

    .summary-row span {
        color: #6b7280;
    }

    .summary-row strong {
        font-weight: 600;
    }

    /* ===== MOBILE BILLING ===== */
    .mobile-billing-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .breakdown-item {
        padding: 10px 0;
        border-bottom: 1px dashed #e5e7eb;
    }

    .discount-item {
        background: rgba(220, 38, 38, .04);
        border-radius: 6px;
        padding: 10px;
    }

    .mobile-total-box {
        margin-top: 16px;
        padding: 14px;
        border-radius: 8px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
    }

    /* ===== STAMP ===== */
    .billing-stamp {
        font-size: 56px;
        font-weight: 900;
        letter-spacing: 6px;
        padding: 18px 36px;
        border: 6px solid;
        border-radius: 12px;
        transform: rotate(-12deg);
        opacity: .25;
        font-family: Impact, Arial Black, sans-serif;
    }

    .billing-stamp.paid {
        color: #dc2626;
        border-color: #dc2626;
    }

    .billing-stamp.unpaid {
        color: #991b1b;
        border-color: #991b1b;
    }

    .billing-stamp.paylater {
        color: #b45309;
        border-color: #b45309;
    }

    .billing-stamp-mobile {
        font-weight: 900;
        font-size: 20px;
        padding: 8px 22px;
        border: 3px solid;
        border-radius: 10px;
        letter-spacing: 3px;
        transform: rotate(-4deg);
    }

    .billing-stamp-mobile.paid {
        color: #dc2626;
        border-color: #dc2626;
    }

    .billing-stamp-mobile.unpaid {
        color: #991b1b;
        border-color: #991b1b;
    }

    .billing-stamp-mobile.paylater {
        color: #b45309;
        border-color: #b45309;
    }
</style>
